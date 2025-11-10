<?php

namespace App\Services;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ActivityService
{
    public function getActivitiesList(): array
    {
        return [
            'activities' => fn() => Activity::where('user_id', Auth::id())
                ->selectedAttributes()
                ->withCount([
                    'activityLinks as open_links_count' => fn($q) => $q->where('is_open', true),
                    'activityLinks as closed_links_count' => fn($q) => $q->where('is_open', false),
                ])
                ->latest()
                ->paginate(8)
                ->withQueryString(),
        ];
    }

    public function getActivityDetails(Activity $activity): array
    {
        return [
            'id' => $activity->id,
            'title' => $activity->title,
            'appUrl' => config('app.url'),
            'links' => Inertia::defer(
                fn() => $activity->activityLinks()
                    ->selectedAttributes()
                    ->orderBy('created_at')
                    ->paginate(8)
                    ->withQueryString()
            ),
        ];
    }
}
