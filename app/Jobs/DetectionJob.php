<?php

namespace App\Jobs;

use App\Models\Detection;
use App\Services\DetectionService;
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
    public function handle(DetectionService $detectionService): void
    {
        Log::info("Starting detection for {$this->activityLinkId}");
        $detectionService->detectAndStore($this->activityLinkId, $this->submissionsData, $this->language);
        Log::info("Detection job completed for {$this->activityLinkId}");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Detection job permanently failed for {$this->activityLinkId}: " . $exception->getMessage());
        // Optional: send notification to user here
    }
}
