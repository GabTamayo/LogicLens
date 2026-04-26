<?php

namespace App\Jobs;

use App\Models\Detection;
use App\Services\ExplanationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExplainDetectionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;

    public $tries = 3;

    public $backoff = [10, 30, 60];

    public function __construct(public string $detectionId) {}

    public function handle(ExplanationService $service): void
    {
        $detection = Detection::find($this->detectionId);

        if (! $detection) {
            Log::warning("Detection not found for explanation: {$this->detectionId}");

            return;
        }

        if ($detection->ai_explanation) {
            Log::info("Explanation already exists for detection: {$this->detectionId}");

            return;
        }

        try {
            Log::info("Generating AI explanation for detection: {$this->detectionId}");

            $explanation = $service->generateExplanation($detection);

            $detection->update([
                'ai_explanation' => $explanation,
                'explanation_generated_at' => now(),
            ]);

            Log::info("AI explanation generated successfully for detection: {$this->detectionId}");
        } catch (\Exception $e) {
            Log::error("Failed to generate explanation for detection {$this->detectionId}: {$e->getMessage()}");
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Explanation job permanently failed for detection {$this->detectionId}: {$exception->getMessage()}");

        Detection::find($this->detectionId)?->update([
            'ai_explanation' => 'Failed to generate explanation. Please try again later.',
        ]);
    }
}
