<?php

namespace App\Http\Controllers;

use App\Actions\GenerateActivityLink;
use App\Http\Requests\ActivityLinkRequest;
use App\Http\Requests\ActivityLinkUpdateRequest;
use App\Models\Activity;
use App\Models\Detection;
use App\Services\ActivityLinkService;
use App\Services\DetectionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLinkController extends Controller
{
    public function store(ActivityLinkRequest $request, Activity $activity, GenerateActivityLink $generateActivityLink)
    {
        $data = $request->validated();
        $generateActivityLink->execute($activity, $data['course_id'], $data['expires_at'] ?? null);

        return redirect()->route('activities.show', $activity);
    }

    public function update(ActivityLinkUpdateRequest $request, Activity $activity, $linkId)
    {
        $link = $activity->activityLinks()->findOrFail($linkId);
        $link->update($request->validated());

        return redirect()->route('activities.show', $activity);
    }

    public function show(Activity $activity, $linkId, Request $request, ActivityLinkService $activityLinkService, DetectionService $detectionService)
    {
        $link = $activity->activityLinks()->selectedAttributes()->with('course:id,name')->findOrFail($linkId);
        $activeTab = $request->get('tab', 'submission');

        $baseData = [
            'link' => $link,
            'activityId' => $activity->id,
            'activityTitle' => $activity->title,
            'activeTab' => $activeTab,
            'hasDetections' => Detection::where('activity_link_id', $link->id)->exists(),
        ];

        $tabData = $activeTab === 'detection'
            ? ['detections' => Inertia::defer(fn () => $detectionService->queryDetections($link, $request->only(['student_name_a', 'student_name_b', 'sort']))), 'submissions' => null]
            : ['submissions' => Inertia::defer(fn () => $activityLinkService->querySubmissions($link, $request)), 'detections' => null];

        return Inertia::render('Submissions/Index', [
            ...$baseData,
            ...$tabData,
            'filters' => $request->only($activeTab === 'detection' ? ['student_name_a', 'student_name_b', 'sort'] : ['student_name', 'student_no', 'sort']),
        ]);
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
