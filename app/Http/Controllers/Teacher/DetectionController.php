<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Jobs\DetectionJob;
use App\Models\Activity;
use App\Models\Detection;
use App\Services\DetectionService;
use Inertia\Inertia;

class DetectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function store(Activity $activity, $linkId, DetectionService $service)
    {
        [$valid, $error, $payload] = $service->validateForDetection($activity, $linkId);

        if (! $valid) {
            return back()->withErrors(['error' => $error]);
        }

        DetectionJob::dispatch(
            $linkId,
            $payload['submissions'],
            $payload['language']
        );

        return redirect()->route('activities.links.show', [
            'activity' => $activity->id,
            'link' => $linkId,
        ]);
    }

    public function show(Detection $detection, DetectionService $service)
    {
        return Inertia::modal('Detections/Show', $service->getDetectionDetail($detection));
    }

    public function flag(Detection $detection)
    {
        $detection->update(attributes: ['flagged' => ! $detection->flagged]);

        return back()->with('success', 'Detection flag updated.');
    }
}
