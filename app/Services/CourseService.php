<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CourseService
{
    public function getCoursesList(?string $search = null, ?string $sort = null): array
    {
        return [
            'courses' => fn () => Course::where('user_id', Auth::id())
                ->when($search, function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                        ->orWhere('access_code', 'like', '%'.$search.'%');
                })
                ->selectedAttributes()
                ->when($sort, function ($query) use ($sort) {
                    match ($sort) {
                        'name_asc' => $query->orderBy('name', 'asc'),
                        'name_desc' => $query->orderBy('name', 'desc'),
                        'oldest' => $query->oldest(),
                        default => $query->latest(),
                    };
                }, function ($query) {
                    $query->latest();
                })
                ->paginate(9)
                ->withQueryString(),
            'filters' => [
                'search' => $search ?? '',
                'sort' => $sort ?? 'newest',
            ],
        ];
    }

    public function getCourseDetails(string $courseId): array
    {
        $course = Auth::user()->courses()
            ->selectedAttributes()
            ->with('user:id,name')
            ->findOrFail($courseId);

        return [
            'course' => $course,
            'activities' => Inertia::defer(fn () => $course->activityLinks()
                ->with(['activity:id,title,language', 'activity.user:id,name'])
                ->latest()
                ->get()
                ->map(fn ($link) => [
                    'id' => $link->id,
                    'activity_id' => $link->activity_id,
                    'activity_title' => $link->activity->title ?? 'N/A',
                    'activity_language' => $link->activity->language ?? 'N/A',
                    'token' => $link->token,
                    'is_open' => $link->is_open,
                    'expires_at' => $link->expires_at,
                    'created_at' => $link->created_at,
                    'submissions_count' => $link->submissions()->count(),
                ])
            ),
            'students' => Inertia::defer(fn () => [
                // Placeholder for students data
                // This will be implemented when enrollment system is added
            ]),
        ];
    }
}
