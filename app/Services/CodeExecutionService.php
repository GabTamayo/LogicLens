<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CodeExecutionService
{
    private string $pistonUrl;

    public function __construct()
    {
        $this->pistonUrl = config('services.piston.url');
    }

    public function execute(array $data): array
    {
        try {
            $response = Http::timeout(30)
                ->post("{$this->pistonUrl}/execute", $data);

            if ($response->failed()) {
                Log::error('Piston API error', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [
                    'error' => 'Piston API error',
                    'message' => $response->body(),
                    'status' => $response->status(),
                ];
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Code execution service error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    public function getRuntimes(): array
    {
        try {
            $response = Http::timeout(10)
                ->get("{$this->pistonUrl}/runtimes");

            if ($response->failed()) {
                return [
                    'error' => 'Piston API error',
                    'message' => $response->body(),
                ];
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Code execution service error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
