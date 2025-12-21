<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmissionDraftRequest;
use App\Services\SubmissionDraftService;

class SubmissionDraftController extends Controller
{
    public function saveDraft(SubmissionDraftRequest $request, string $token, SubmissionDraftService $service)
    {
        $result = $service->saveDraft($token, $request->validated());

        return response()->json($result);
    }

    public function getDraft(string $token, SubmissionDraftService $service)
    {
        $result = $service->getDraft($token);

        return response()->json($result);
    }
}
