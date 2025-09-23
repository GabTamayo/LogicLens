<?php

namespace App\Http\Controllers;

use App\Actions\GenerateActivityLink;
use App\Models\Activity;
use App\Models\ActivityLink;
use Illuminate\Http\Request;

class ActivityLinkController extends Controller
{
    public function store(Request $request, Activity $activity, GenerateActivityLink $generateActivityLink)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $generateActivityLink->execute($activity, $request->input('name'));

        return redirect()->route('activities.show', $activity);
    }

    public function update(Request $request, Activity $activity, $link)
    {
        $link = $activity->activityLinks()->findOrFail($link);

        $switch = $request->validate([
            'is_open' => 'required|boolean',
        ]);

        $link->update($switch);

        return redirect()->route('activities.show', $activity);
    }

    public function show(Activity $activity, $linkId)
    {
        $link = $activity->activityLinks()
            ->with(['submissions' => function ($query) {
                $query->latest();
            }])
            ->findOrFail($linkId);

        return inertia('Submissions/Index', [
            'activityId' => $activity->id,
            'activityTitle' => $activity->title,
            'link' => $link,
            'submissions' => $link->submissions,
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
