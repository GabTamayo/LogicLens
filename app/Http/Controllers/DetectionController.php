<?php

namespace App\Http\Controllers;

use App\Jobs\DetectionJob;
use App\Models\Activity;
use App\Models\ActivityLink;
use App\Models\Detection;
use App\Services\DetectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DetectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function store(Activity $activity, $linkId)
    {
        $activityLink = ActivityLink::with('submissions')->findOrFail($linkId);

        $submissionsData = $activityLink->submissions
            ->filter(fn($s) => $s->file_path && Storage::disk('public')->exists($s->file_path))
            ->map(fn($s) => ['id' => $s->id, 'file_path' => $s->file_path])
            ->values()
            ->toArray();

        if (count($submissionsData) < 2) {
            return back()->withErrors(['error' => 'At least 2 submissions are required for detection.']);
        }

        $language = $activityLink->submissions->first()->language;

        DetectionJob::dispatch($linkId, $submissionsData, $language);

        return redirect()->route('activities.links.show', ['activity' => $activity->id, 'link' => $linkId]);
    }

    public function index(Activity $activity, ActivityLink $link, Request $request, DetectionService $detectionService)
    {
        $hasDetections = Detection::where('activity_link_id', $link->id)->exists();
        if (!$hasDetections) {
            abort(404);
        }

        $data = $detectionService->getDetections($activity, $link, $request);
        return Inertia::render('Submissions/Show', $data);
    }
}
