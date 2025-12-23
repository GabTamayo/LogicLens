<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Requests\SubmissionRequest;
use App\Http\Controllers\Controller;
use App\Models\ActivityLink;
use App\Models\Submission;
use App\Services\SubmissionService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class SubmissionController extends Controller
{
    public function create($token, SubmissionService $service)
    {
        $activityLink = ActivityLink::with(['activity', 'course'])->where('token', $token)->firstOrFail();

        if (! $activityLink->is_open) {
            abort(403, 'This activity is no longer accepting submissions.');
        }

        $data = $service->getSubmissionFormData($activityLink, Auth::user());

        return Inertia::render('Submissions/Create', $data);
    }

    public function store(SubmissionRequest $request, $token, SubmissionService $submissionService)
    {
        $activityLink = ActivityLink::where('token', $token)->firstOrFail();

        $submissionService->storeSubmission($activityLink, Auth::user(), $request->validated());

        return redirect()->back();
    }

    public function destroy(Submission $submission)
    {
        $submission->delete();
        inertia()->clearHistory();

        return redirect()->back();
    }
}
