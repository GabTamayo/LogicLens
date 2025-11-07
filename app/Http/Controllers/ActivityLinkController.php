<?php

namespace App\Http\Controllers;

use App\Actions\GenerateActivityLink;
use App\Http\Requests\ActivityLinkRequest;
use App\Http\Requests\ActivityLinkUpdateRequest;
use App\Models\Activity;
use App\Models\ActivityLink;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLinkController extends Controller
{
    public function store(ActivityLinkRequest $request, Activity $activity, GenerateActivityLink $generateActivityLink)
    {
        $generateActivityLink->execute($activity, $request->validated()['name']);
        return redirect()->route('activities.show', $activity);
    }

    public function update(ActivityLinkUpdateRequest $request, Activity $activity, $linkId)
    {
        $link = $activity->activityLinks()->findOrFail($linkId);
        $link->update($request->validated());
        return redirect()->route('activities.show', $activity);
    }

    public function show(Activity $activity, $linkId, Request $request)
    {
        $link = $activity->activityLinks()->selectedAttributes()->findOrFail($linkId);

        return Inertia::render('Submissions/Index', [
            'activityId' => $activity->id,
            'activityTitle' => $activity->title,
            'link' => $link,
            'filters' => $request->only(['student_name', 'student_no']),
            'submissions' => Inertia::defer(function () use ($link, $request) {
                $submissions = $link->submissions()
                    ->selectedAttributes()
                    ->orderBy('created_at')
                    ->when($request->input('student_name'), function ($query, $search) {
                        $query->where('student_name', 'like', '%' . $search . '%');
                    })
                    ->when($request->input('student_no'), function ($query, $search) {
                        $query->where('student_no', 'like', '%' . $search . '%');
                    })
                    ->paginate(10)
                    ->withQueryString();

                $submissions->getCollection()->transform(function ($submission) {
                    if ($submission->file_path && \Storage::disk('public')->exists($submission->file_path)) {
                        $submission->file_content = \Storage::disk('public')->get($submission->file_path);
                        $submission->file_extension = pathinfo($submission->file_path, PATHINFO_EXTENSION);
                    }
                    return $submission;
                });

                return $submissions;
            }),
        ]);
    }

    public function destroy($activityId, $linkId)
    {
        $link = ActivityLink::where('id', $linkId)
            ->where('activity_id', $activityId)
            ->firstOrFail();

        $link->delete();

        return redirect()->route('activities.show', ['activity' => $activityId]);
    }
}
