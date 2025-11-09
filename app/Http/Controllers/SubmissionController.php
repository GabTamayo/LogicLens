<?php

namespace App\Http\Controllers;

use App\Actions\OneTimeSubmission;
use App\Enums\ProgrammingLanguage;
use App\Http\Requests\SubmissionRequest;
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
        $activityLink = ActivityLink::with('activity')->where('token', $token)->firstOrFail();
        $language = $activityLink->activity->language;
        $allowedExtensions = ProgrammingLanguage::fileExtensions($language);

        return Inertia::render('Submissions/Create', [
            'bgImage' => asset('storage/images/clonewave-bg.jpg'),
            'name' => $activityLink->name,
            'activityName' => $activityLink->activity->title,
            'token' => $token,
            'allowedExtensions' => $allowedExtensions,
        ]);
    }

    public function store(SubmissionRequest $request, $token)
    {
        $activityLink = ActivityLink::where('token', $token)->firstOrFail();
        $validated = $request->validated();
        $file = $request->file('code_file');
        $path = $file->store('submissions', 'public');

        $language = ProgrammingLanguage::fromFileExtension($file->getClientOriginalExtension());

        $activityLink->submissions()->create([
            ...$validated,
            'file_path' => $path,
            'language'  => $language,
        ]);

        return redirect()->back()->with('success', 'Submission successful!');
    }
}
