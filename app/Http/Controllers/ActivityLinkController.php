<?php

namespace App\Http\Controllers;

use App\Actions\GenerateActivityLink;
use App\Http\Requests\ActivityLinkRequest;
use App\Http\Requests\ActivityLinkUpdateRequest;
use App\Models\Activity;
use App\Models\ActivityLink;
use Illuminate\Http\Request;

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
        $submissions = $link->submissions()
            ->selectedAttributes()
            ->when($request->input('student_name'), function ($query, $search) {
                $query->where('student_name', 'like', '%' . $search . '%');
            })
            ->when($request->input('student_no'), function ($query, $search) {
                $query->where('student_no', 'like', '%' . $search . '%');
            })
            ->paginate(10)
            ->withQueryString();

        return inertia('Submissions/Index', [
            'activityId' => $activity->id,
            'activityTitle' => $activity->title,
            'link' => $link,
            'submissions' => $submissions,
            'filters' => $request->only(['student_name', 'student_no']),
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
