<?php

namespace App\Http\Controllers;

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
}
