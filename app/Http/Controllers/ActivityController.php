<?php

namespace App\Http\Controllers;

use App\Enums\ProgrammingLanguage;
use App\Http\Requests\ActivityRequest;
use App\Models\Activity;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('Activities/Index', [
            'activities' => function () {
                return Activity::where('user_id', Auth::id())
                    ->selectedAttributes()
                    ->withCount([
                        'activityLinks as open_links_count' => fn($q) => $q->where('is_open', true),
                        'activityLinks as closed_links_count' => fn($q) => $q->where('is_open', false)
                    ])
                    ->latest()
                    ->paginate(8)
                    ->withQueryString();
            }
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Activities/Create', [
            'languages' => ProgrammingLanguage::asSelectArray(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ActivityRequest $request)
    {
        $request->user()->activities()->create($request->validated());
        return redirect()->route('activities.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Activity $activity)
    {
        return Inertia::render('Activities/Show', [
            'id' => $activity->id,
            'title' => $activity->title,
            'appUrl' => config('app.url'),
            'links' => Inertia::defer(function () use ($activity) {
                return $activity->activityLinks()
                    ->selectedAttributes()
                    ->orderBy('name')
                    ->paginate(8)
                    ->withQueryString();
            }),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Activity $activity)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Activity $activity)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Activity $activity)
    {
        $activity->delete();

        return redirect()->route('activities.index');
    }
}
