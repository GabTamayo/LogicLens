<?php

namespace App\Jobs;

use App\Models\Detection;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DetectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 2;
    public $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $activityLinkId, public array $submissionsData, public string $language)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Starting detection for activity link {$this->activityLinkId}");

        $submissionsWithContent = collect($this->submissionsData)
            ->map(fn($s) => $this->loadSubmissionContent($s))
            ->filter()
            ->values()
            ->toArray();

        if (empty($submissionsWithContent)) {
            Log::warning("No valid files to process for {$this->activityLinkId}");
            return;
        }

        $fastApiUrl = config('services.fastapi.url', 'http://localhost:8001');

        $response = Http::timeout(120)->post("{$fastApiUrl}/detect", [
            'submissions' => $submissionsWithContent,
            'language' => $this->language,
        ]);

        Log::info("FastAPI response status: " . $response->status());

        if ($response->successful()) {
            $this->storeDetections($response->json()['results'] ?? []);
            Log::info("Detection completed for {$this->activityLinkId}");
        } else {
            $body = $response->body();
            Log::error("Detection API failed: {$body}");
            throw new \Exception("Detection API returned error: {$response->status()}");
        }
    }

    private function loadSubmissionContent(array $submission): ?array
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

    private function storeDetections(array $results): void
    {
        if (empty($results)) return;

        DB::transaction(function () use ($results) {
            Detection::where('activity_link_id', $this->activityLinkId)->delete();

            $data = collect($results)->map(fn($r) => [
                'id' => (string) Str::uuid(),
                'activity_link_id' => $this->activityLinkId,
                'submission_a_id' => $r['submission_a_id'],
                'submission_b_id' => $r['submission_b_id'],
                'similarity_score' => $r['similarity_score'],
                'created_at' => now(),
                'updated_at' => now(),
            ])->toArray();

            Detection::insert($data);
        });
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Detection job permanently failed for {$this->activityLinkId}: " . $exception->getMessage());
        // Optional: send notification to user here
    }
}
