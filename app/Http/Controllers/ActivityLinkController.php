<?php

namespace App\Http\Controllers;

use App\Actions\GenerateActivityLink;
use App\Http\Requests\ActivityLinkRequest;
use App\Http\Requests\ActivityLinkUpdateRequest;
use App\Models\Activity;
use App\Models\ActivityLink;
use App\Services\ActivityLinkService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLinkController extends Controller
{
    public function store(ActivityLinkRequest $request, Activity $activity, GenerateActivityLink $generateActivityLink)
    {
        $data = $request->validated();
        $generateActivityLink->execute($activity, $data['name'], $data['expires_at'] ?? null);
        return redirect()->route('activities.show', $activity);
    }

    public function update(ActivityLinkUpdateRequest $request, Activity $activity, $linkId)
    {
        $link = $activity->activityLinks()->findOrFail($linkId);
        $link->update($request->validated());
        return redirect()->route('activities.show', $activity);
    }

    public function show(Activity $activity, $linkId, Request $request, ActivityLinkService $activityLinkService)
    {
        $link = $activity->activityLinks()->selectedAttributes()->findOrFail($linkId);
        $data = $activityLinkService->getSubmissions($link, $request);
        return Inertia::render('Submissions/Index', $data);
    }

    public function destroy(Activity $activity, $linkId)
    {
        $link = $activity->activityLinks()->findOrFail($linkId);
        $link->delete();
        inertia()->clearHistory();
        return redirect()->route('activities.show', $activity);
    }

    public function removeDeadline(Activity $activity, $linkId)
    {
        $link = $activity->activityLinks()->findOrFail($linkId);
        $link->update(['expires_at' => null]);
        return redirect()->route('activities.show', $activity);
    }
}
