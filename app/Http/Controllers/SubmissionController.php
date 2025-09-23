<?php

namespace App\Http\Controllers;

use App\Actions\OneTimeSubmission;
use App\Models\ActivityLink;
use App\Models\Submission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubmissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function create($token)
    {
        $activityLink = ActivityLink::with('activity')
            ->where('token', $token)
            ->firstOrFail();

        return Inertia::render('Submissions/Create', [
            'bgImage' => asset('storage/images/clonewave-bg.jpg'),
            'name' => $activityLink->name,
            'activityName' => $activityLink->activity->title,
            'token' => $token,
        ]);
    }

    public function store(Request $request, $token, OneTimeSubmission $oneTimeSubmission)
    {
        $activityLink = ActivityLink::where('token', $token)->firstOrFail();

        $validated = $request->validate([
            'student_name'  => 'required|string|max:255',
            'student_email' => 'required|email|max:255',
            'student_no'    => 'required|string|max:50',
            'code_file'          => 'required|file|mimetypes:text/plain,text/x-java-source,application/octet-stream|max:10240',
        ]);

        if ($oneTimeSubmission->alreadySubmitted($activityLink->id, $validated['student_email'])) {
            return redirect()
                ->back()
                ->withErrors(['student_email' => 'You have already submitted for this activity.']);
        }

        $path = $request->file('code_file')->store('submissions', 'public');

        Submission::create([
            'activity_link_id' => $activityLink->id,
            'student_name'     => $validated['student_name'],
            'student_email'    => $validated['student_email'],
            'student_no'       => $validated['student_no'],
            'file_path'        => $path,
        ]);

        return redirect()
            ->route('submissions.create', ['token' => $token])
            ->with('success', 'Submission successful!');
    }
}
