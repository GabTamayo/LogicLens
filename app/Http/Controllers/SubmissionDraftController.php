<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubmissionDraftRequest;
use App\Services\SubmissionDraftService;

class SubmissionDraftController extends Controller
{
    public function saveDraft(SubmissionDraftRequest $request, string $token, SubmissionDraftService $service): void
    {
        $service->saveDraft($token, $request->validated());
    }
}
