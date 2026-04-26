<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubmissionRequest;
use App\Models\ActivityLink;
use App\Models\ExamViolation;
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

    public function violations(Submission $submission)
    {
        $violations = ExamViolation::where('submission_id', $submission->id)
            ->orderBy('violated_at', 'desc')
            ->get()
            ->map(function ($violation) {
                return [
                    'id' => $violation->id,
                    'type' => $violation->violation_type,
                    'details' => $violation->details,
                    'timestamp' => $violation->created_at->format('M d, Y h:i:s A'),
                    'ip_address' => $violation->ip_address,
                ];
            });

        return response()->json([
            'violations' => $violations,
            'total' => $violations->count(),
        ]);
    }
}
