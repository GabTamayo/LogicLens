<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivityLink;
use App\Models\Detection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DetectionService
{
    protected string $fastApiUrl;

    public function __construct()
    {
        $this->fastApiUrl = config('services.similarity_detector.url');
    }

    public function validateForDetection(Activity $activity, $linkId): array
    {
        $link = ActivityLink::with(['submissions'])->findOrFail($linkId);

        if ($link->is_open) {
            return [false, 'The link must be closed before running detection.', null];
        }

        $submissions = $link->submissions->filter(fn ($s) => ! empty($s->code_content));

        if ($submissions->count() < 2) {
            return [false, 'At least 2 submissions are required for detection.', null];
        }

        if (! $submissions->every(fn ($s) => $s->language === $activity->language)) {
            return [false, "One or more submissions use a different programming language than {$activity->language}.", null];
        }

        // FIX: Map code_content instead of file_path
        $payload = [
            'submissions' => $submissions
                ->map(fn ($s) => [
                    'id' => $s->id,
                    'code_content' => $s->code_content,  // Changed from file_path
                    'language' => $s->language,
                ])
                ->values()
                ->toArray(),
            'language' => $activity->language,
        ];

        return [true, null, $payload];
    }

    public function getDetections(Activity $activity, ActivityLink $link, Request $request): array
    {
        $filters = $request->only(['student_name_a', 'student_name_b', 'min_score']);

        return [
            'activityId' => $activity->id,
            'activityTitle' => $activity->title,
            'link' => $link,
            'filters' => $filters,
            'detections' => Inertia::defer(fn () => $this->queryDetections($link, $filters)),
        ];
    }

    public function queryDetections(ActivityLink $link, array $filters)
    {
        $sort = $filters['sort'] ?? 'score_desc';

        $query = Detection::forLink($link->id)
            ->filter($filters)
            ->selectedAttributes()
            ->with(['submissionA.user', 'submissionB.user'])
            ->orderByDesc('flagged');

        match ($sort) {
            'score_asc' => $query->orderBy('avg_score', 'asc'),
            default => $query->orderBy('avg_score', 'desc'),
        };

        return $query->paginate(10)
            ->through(fn ($detection) => $this->transformDetectionSummary($detection))
            ->withQueryString();
    }

    private function transformDetectionSummary($detection): array
    {
        return [
            'id' => $detection->id,
            'submission_a' => [
                'student_name' => $detection->submissionA->user->name,
            ],
            'submission_b' => [
                'student_name' => $detection->submissionB->user->name,
            ],
            'avg_score' => $detection->avg_score,
            'flagged' => $detection->flagged,
        ];
    }

    private function transformDetection($detection): array
    {
        return [
            'id' => $detection->id,
            'submission_a' => $this->transformSubmission($detection->submissionA),
            'submission_b' => $this->transformSubmission($detection->submissionB),
            'seq_score' => $detection->seq_score,
            'struct_score' => $detection->struct_score,
            'avg_score' => $detection->avg_score,
            'line_matches' => $detection->line_matches,
        ];
    }

    private function transformSubmission($submission): ?array
    {
        if (! $submission) {
            return null;
        }

        return [
            'id' => $submission->id,
            'student_name' => $submission->user->name,
            'language' => $submission->language,
        ];
    }

    public function getDetectionDetail(Detection $detection): array
    {
        $detection->load(['submissionA.user', 'submissionB.user']);

        return [
            'detection' => $this->transformDetection($detection),
            'fileA' => $detection->submissionA->code_content,
            'fileB' => $detection->submissionB->code_content,
        ];
    }

    public function detectAndStore(string $activityLinkId, array $submissionsData, string $language): void
    {
        $submissions = $this->prepareSubmissions($submissionsData);

        if (empty($submissions)) {
            Log::warning("No valid submissions for {$activityLinkId}");

            return;
        }

        Log::info('Sending to FastAPI', [
            'url' => $this->fastApiUrl,
            'submission_count' => count($submissions),
            'language' => $language,
        ]);

        $response = Http::timeout(120)->post("{$this->fastApiUrl}/detect", [
            'submissions' => $submissions,
            'language' => $language,
        ]);

        if ($response->successful()) {
            $results = $response->json()['results'] ?? [];
            Log::info('FastAPI response received', ['result_count' => count($results)]);
            $this->storeDetections($activityLinkId, $results);
        } else {
            Log::error('Detection API failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \Exception("Detection API returned error: {$response->status()}");
        }
    }

    private function prepareSubmissions(array $submissionsData): array
    {
        return collect($submissionsData)
            ->map(fn ($s) => $this->validateSubmission($s))
            ->filter()
            ->values()
            ->toArray();
    }

    private function validateSubmission(array $submission): ?array
    {
        if (empty($submission['code_content'])) {
            Log::warning("Empty code content for submission: {$submission['id']}");

            return null;
        }

        $size = strlen($submission['code_content']);
        if ($size > 10 * 1024 * 1024) {
            Log::warning("Code too large, skipping: {$submission['id']}");

            return null;
        }

        return [
            'id' => $submission['id'],
            'code_content' => $submission['code_content'],
        ];
    }

    private function storeDetections(string $activityLinkId, array $results): void
    {
        if (empty($results)) {
            Log::warning("No detection results to store for {$activityLinkId}");

            return;
        }

        DB::transaction(function () use ($activityLinkId, $results) {
            Detection::where('activity_link_id', $activityLinkId)->delete();

            $data = collect($results)->map(fn ($r) => [
                'id' => (string) Str::uuid(),
                'activity_link_id' => $activityLinkId,
                'submission_a_id' => $r['submission_a_id'],
                'submission_b_id' => $r['submission_b_id'],
                'seq_score' => $r['seq_score'],
                'struct_score' => $r['struct_score'],
                'avg_score' => $r['avg_score'],
                'line_matches' => json_encode($r['line_matches']),
                'flagged' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray();

            Detection::insert($data);
            Log::info("Stored {count} detections for {$activityLinkId}", ['count' => count($data)]);
        });
    }
}
