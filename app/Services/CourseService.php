<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Support\Facades\Auth;

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
        ];
    }

    public function queryActivities($course, array $params): array
    {
        $page = $params['page'] ?? 1;

        return $course->activityLinks()
            ->with(['activity:id,title,language,content', 'activity.user:id,name'])
            ->latest()
            ->paginate(10, ['*'], 'page', $page)
            ->withQueryString()
            ->through(fn ($link) => [
                'id' => $link->id,
                'activity_id' => $link->activity_id,
                'activity_title' => $link->activity->title ?? 'N/A',
                'activity_language' => $link->activity->language ?? 'N/A',
                'activity_content' => $link->activity->content ?? null,
                'token' => $link->token,
                'is_open' => $link->is_open,
                'expires_at' => $link->expires_at,
                'created_at' => $link->created_at,
                'submissions_count' => $link->submissions()->count(),
            ])
            ->toArray();
    }

    public function queryStudents($course, array $params): array
    {
        $page = $params['page'] ?? 1;

        return $course->students()
            ->select('users.id', 'users.name', 'users.email', 'course_user.enrolled_at')
            ->latest('course_user.enrolled_at')
            ->paginate(10, ['*'], 'page', $page)
            ->withQueryString()
            ->through(fn ($student) => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
                'enrolled_at' => $student->pivot->enrolled_at,
            ])
            ->toArray();
    }
}
