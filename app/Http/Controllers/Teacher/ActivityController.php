<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\ProgrammingLanguage;
use App\Http\Controllers\Controller;
use App\Http\Requests\ActivityRequest;
use App\Http\Requests\ActivityUpdateContentRequest;
use App\Models\Activity;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ActivityController extends Controller
{
    public function index(Request $request, ActivityService $activityService)
    {
        $data = $activityService->getActivitiesList(
            $request->input('language'),
            $request->input('search'),
            $request->input('sort')
        );

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
        $validated = $request->validated();

        $activity = Auth::user()->activities()->create([
            'title' => $validated['title'],
            'language' => $validated['language'],
            'content' => $validated['content'] ?? null,
        ]);

        if (! empty($validated['test_cases'])) {
            foreach ($validated['test_cases'] as $index => $testCase) {
                $activity->testCases()->create([
                    'title' => $testCase['title'],
                    'input' => $testCase['input'] ?? null,
                    'output' => $testCase['output'],
                    'score' => $testCase['score'],
                    'order' => $index,
                ]);
            }
        }

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

    public function update(ActivityUpdateContentRequest $request, Activity $activity)
    {
        $validated = $request->validated();

        $updateData = [];

        if (isset($validated['content'])) {
            $activity->update(['content' => $validated['content']]);
        }

        if (isset($validated['test_cases'])) {
            $activity->testCases()->delete();

            foreach ($validated['test_cases'] as $index => $testCase) {
                $activity->testCases()->create([
                    'title' => $testCase['title'],
                    'input' => $testCase['input'] ?? null,
                    'output' => $testCase['output'],
                    'score' => $testCase['score'],
                    'order' => $index,
                ]);
            }
        }

        return back();
    }

    public function destroy(Activity $activity)
    {
        $activity->delete();
        inertia()->clearHistory();

        return redirect()->route('activities.index');
    }
}
