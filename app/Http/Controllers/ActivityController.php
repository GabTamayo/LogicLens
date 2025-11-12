<?php

namespace App\Http\Controllers;

use App\Enums\ProgrammingLanguage;
use App\Http\Requests\ActivityRequest;
use App\Models\Activity;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    public function index(ActivityService $activityService)
    {
        $data = $activityService->getActivitiesList();
        return Inertia::render('Activities/Index', $data);
    }

    public function create()
    {
        return Inertia::render('Activities/Create', [
            'languages' => ProgrammingLanguage::asSelectArray(),
        ]);
    }

    public function store(ActivityRequest $request)
    {
        Auth::user()->activities()->create($request->validated());
        return redirect()->route('activities.index');
    }

    public function show(Activity $activity, ActivityService $activityService)
    {
        $data = $activityService->getActivityDetails($activity);
        return Inertia::render('Activities/Show', $data);
    }

    public function edit(Activity $activity)
    {
        //
    }

    public function update(Request $request, Activity $activity)
    {
        //
    }

    public function destroy(Activity $activity)
    {
        $activity->delete();
        return redirect()->route('activities.index');
    }
}
