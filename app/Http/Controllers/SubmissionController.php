<?php

namespace App\Http\Controllers;

use App\Actions\OneTimeSubmission;
use App\Enums\ProgrammingLanguage;
use App\Http\Requests\SubmissionRequest;
use App\Models\ActivityLink;
use App\Models\Submission;
use App\Services\SubmissionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function create($token, SubmissionService $service)
    {
        $activityLink = ActivityLink::where('token', $token)->firstOrFail();
        if (! $activityLink->is_open) {
            abort(404);
        }

        $data = $service->getSubmissionFormData($token);
        return Inertia::render('Submissions/Create', $data);
    }

    public function store(SubmissionRequest $request, $token, SubmissionService $submissionService)
    {
        $activityLink = ActivityLink::where('token', $token)->firstOrFail();
        $submissionService->storeSubmission($activityLink, $request->validated(), $request->file('code_file'));

        return redirect()->back();
    }
}
