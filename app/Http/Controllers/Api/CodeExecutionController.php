<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CodeExecutionRequest;
use App\Services\CodeExecutionService;

class CodeExecutionController extends Controller
{
    public function execute(CodeExecutionRequest $request, CodeExecutionService $service)
    {
        try {
            $result = $service->execute($request->validated());

            if (isset($result['error'])) {
                return response()->json($result, $result['status'] ?? 500);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to connect to code execution service',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function runtimes(CodeExecutionService $service)
    {
        try {
            $result = $service->getRuntimes();

            if (isset($result['error'])) {
                return response()->json($result, 500);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to connect to code execution service',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
