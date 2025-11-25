<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivityLink;
use App\Models\Detection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DetectionService
{
    public function validateForDetection(Activity $activity, $linkId): array
    {
        $link = ActivityLink::with('submissions')->findOrFail($linkId);

        $submissions = $link->submissions->filter(
            fn($s) =>
            $s->file_path && Storage::disk('public')->exists($s->file_path)
        );

        if ($submissions->count() < 2) {
            return [false, "At least 2 submissions are required for detection.", null];
        }

        if (! $submissions->every(fn($s) => $s->language === $activity->language)) {
            return [false, "One or more submissions use a different programming language than {$activity->language}.", null];
        }

        $payload = [
            'submissions' => $submissions
                ->map(fn($s) => ['id' => $s->id, 'file_path' => $s->file_path, 'language' => $s->language])
                ->values()
                ->toArray(),
            'language' => $activity->language,
        ];

        return [true, null, $payload];
    }

    public function hasDetections(ActivityLink $link): bool
    {
        return Detection::where('activity_link_id', $link->id)->exists();
    }

    public function getDetections(Activity $activity, ActivityLink $link, Request $request): array
    {
        $filters = $request->only(['student_name_a', 'student_name_b', 'min_score']);

        return [
            'activityId' => $activity->id,
            'activityTitle' => $activity->title,
            'activityDate' => $activity->created_at->format('M d, Y'),
            'link' => [
                'id' => $link->id,
                'name' => $link->name,
            ],
            'filters' => $filters,
            'detections' => Inertia::defer(fn() => $this->queryDetections($link, $filters)),
        ];
    }

    private function queryDetections(ActivityLink $link, array $filters)
    {
        $query = Detection::forLink($link->id)
            ->filter($filters)
            ->selectedAttributes()
            ->with(['submissionA', 'submissionB'])
            ->orderByDesc('flagged')
            ->orderByDesc('avg_score');

        return $query->paginate(10)
            ->through(fn($detection) => $this->transformDetectionSummary($detection))
            ->withQueryString();
    }

    private function transformDetectionSummary($detection): array
    {
        return [
            'id' => $detection->id,
            'submission_a' => [
                'student_name' => $detection->submissionA->student_name,
                'student_no' => $detection->submissionA->student_no,
            ],
            'submission_b' => [
                'student_name' => $detection->submissionB->student_name,
                'student_no' => $detection->submissionB->student_no,
            ],
            'avg_score' => $detection->avg_score,
            'flagged' => $detection->flagged,
            'created_at' => $detection->created_at,
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
        if (!$submission) return null;

        return [
            'id' => $submission->id,
            'student_name' => $submission->student_name,
            'student_no' => $submission->student_no,
            'language' => $submission->language,
        ];
    }

    public function getDetectionDetail(Detection $detection): array
    {
        $detection->load(['submissionA', 'submissionB']);

        return [
            'detection' => $this->transformDetection($detection),
            'fileA' => $this->loadFile($detection->submissionA->file_path),
            'fileB' => $this->loadFile($detection->submissionB->file_path),
        ];
    }

    private function loadFile(string $path = null): ?string
    {
        return ($path && Storage::disk('public')->exists($path))
            ? Storage::disk('public')->get($path)
            : null;
    }

    protected string $fastApiUrl;

    public function __construct()
    {
        $this->fastApiUrl = config('services.plagiarism_detector.url');
    }

    public function detectAndStore(string $activityLinkId, array $submissionsData, string $language): void
    {
        $submissions = $this->loadSubmissions($submissionsData);

        if (empty($submissions)) {
            Log::warning("No valid submissions for {$activityLinkId}");
            return;
        }

        $response = Http::timeout(120)->post("{$this->fastApiUrl}/detect", [
            'submissions' => $submissions,
            'language' => $language,
        ]);

        if ($response->successful()) {
            $this->storeDetections($activityLinkId, $response->json()['results'] ?? []);
        } else {
            Log::error("Detection API failed: {$response->body()}");
            throw new \Exception("Detection API returned error: {$response->status()}");
        }
    }

    private function loadSubmissions(array $submissionsData): array
    {
        return collect($submissionsData)
            ->map(fn($s) => $this->loadContent($s))
            ->filter()
            ->values()
            ->toArray();
    }

    private function loadContent(array $submission): ?array
    {
        if (!Storage::disk('public')->exists($submission['file_path'])) return null;

        $size = Storage::disk('public')->size($submission['file_path']);
        if ($size > 10 * 1024 * 1024) { // 10MB limit
            Log::warning("File too large, skipping: {$submission['id']}");
            return null;
        }

        return [
            'id' => $submission['id'],
            'file_content' => Storage::disk('public')->get($submission['file_path']),
        ];
    }

    private function storeDetections(string $activityLinkId, array $results): void
    {
        if (empty($results)) return;

        DB::transaction(function () use ($activityLinkId, $results) {
            Detection::where('activity_link_id', $activityLinkId)->delete();

            foreach ($results as &$r) {
                if ($r['submission_a_id'] > $r['submission_b_id']) {
                    [$r['submission_a_id'], $r['submission_b_id']] = [$r['submission_b_id'], $r['submission_a_id']];
                }
            }

            $data = collect($results)->map(fn($r) => [
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
        });
    }
}
