<?php

namespace App\Services;

use App\Enums\ProgrammingLanguage;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ActivityService
{
    public function getActivitiesList(?string $language = null): array
    {
        return [
            'activities' => fn () => Activity::where('user_id', Auth::id())
                ->when($language && $language !== 'all', function ($query) use ($language) {
                    $query->where('language', ProgrammingLanguage::request(ProgrammingLanguage::response($language)));
                })
                ->selectedAttributes()
                ->withCount([
                    'activityLinks as open_links_count' => fn ($q) => $q->where('is_open', true),
                    'activityLinks as closed_links_count' => fn ($q) => $q->where('is_open', false),
                ])
                ->latest()
                ->paginate(9)
                ->withQueryString(),
            'filters' => [
                'language' => $language ?? 'all',
            ],
        ];
    }

    public function getActivityDetails(Activity $activity): array
    {
        return [
            'id' => $activity->id,
            'title' => $activity->title,
            'language_text' => $activity->language_text,
            'appUrl' => config('app.url'),
            'links' => Inertia::defer(
                fn () => $activity->activityLinks()
                    ->selectedAttributes()
                    ->withCount('submissions')
                    ->orderBy('created_at')
                    ->paginate(8)
                    ->withQueryString()
            ),
        ];
    }
}
