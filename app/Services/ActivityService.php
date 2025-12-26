<?php

namespace App\Services;

use App\Enums\ProgrammingLanguage;
use App\Models\Activity;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ActivityService
{
    public function getActivitiesList(?string $language = null, ?string $search = null, ?string $sort = null): array
    {
        return [
            'activities' => fn () => Activity::where('user_id', Auth::id())
                ->when($language && $language !== 'all', function ($query) use ($language) {
                    $query->where('language', ProgrammingLanguage::request(ProgrammingLanguage::response($language)));
                })
                ->when($search, function ($query) use ($search) {
                    $query->where('title', 'like', '%'.$search.'%');
                })
                ->selectedAttributes()
                ->withCount([
                    'activityLinks as open_links_count' => fn ($q) => $q->where('is_open', true),
                    'activityLinks as closed_links_count' => fn ($q) => $q->where('is_open', false),
                ])
                ->when($sort, function ($query) use ($sort) {
                    match ($sort) {
                        'title_asc' => $query->orderBy('title', 'asc'),
                        'title_desc' => $query->orderBy('title', 'desc'),
                        'oldest' => $query->oldest(),
                        default => $query->latest(),
                    };
                }, function ($query) {
                    $query->latest();
                })
                ->paginate(9)
                ->withQueryString(),
            'filters' => [
                'language' => $language ?? 'all',
                'search' => $search ?? '',
                'sort' => $sort ?? 'newest',
            ],
        ];
    }

    public function getActivityDetails(Activity $activity): array
    {
        // Get IDs of courses that already have links for this activity
        $usedCourseIds = $activity->activityLinks()->pluck('course_id')->toArray();

        return [
            'id' => $activity->id,
            'title' => $activity->title,
            'language_text' => $activity->language_text,
            'content' => $activity->content,
            'appUrl' => config('app.url'),
            'courses' => Course::where('user_id', Auth::id())
                ->where('is_active', true)
                ->whereNotIn('id', $usedCourseIds)
                ->select('id', 'name')
                ->orderBy('name')
                ->get(),
            'test_cases' => $activity->testCases()
                ->orderBy('order')
                ->get()
                ->map(fn ($testCase) => [
                    'id' => $testCase->id,
                    'title' => $testCase->title,
                    'input' => $testCase->input,
                    'output' => $testCase->output,
                    'score' => $testCase->score,
                ]),
            'links' => Inertia::defer(
                fn () => $activity->activityLinks()
                    ->selectedAttributes()
                    ->with('course:id,name')
                    ->withCount('submissions')
                    ->orderBy('created_at')
                    ->paginate(6)
                    ->withQueryString()
            ),
        ];
    }
}
