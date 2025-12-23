<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Support\Facades\Auth;

class StudentCourseService
{
    public function getEnrolledCourses(?string $search = null, ?string $sort = null): array
    {
        return [
            'enrolledCourses' => fn () => Auth::user()
                ->enrolledCourses()
                ->withPivot('enrolled_at')
                ->with('user:id,name')
                ->when($search, function ($query) use ($search) {
                    $query->where('courses.name', 'like', '%'.$search.'%');
                })
                ->select('courses.id', 'courses.user_id', 'courses.name', 'courses.created_at')
                ->when($sort, function ($query) use ($sort) {
                    match ($sort) {
                        'name_asc' => $query->orderBy('courses.name', 'asc'),
                        'name_desc' => $query->orderBy('courses.name', 'desc'),
                        'oldest' => $query->orderBy('course_user.enrolled_at', 'asc'),
                        default => $query->orderBy('course_user.enrolled_at', 'desc'),
                    };
                }, function ($query) {
                    $query->orderBy('course_user.enrolled_at', 'desc');
                })
                ->paginate(9)
                ->withQueryString()
                ->through(fn ($course) => [
                    'id' => $course->id,
                    'user_id' => $course->user_id,
                    'name' => $course->name,
                    'enrolled_at' => $course->pivot->enrolled_at,
                    'user' => $course->user,
                ]),
            'filters' => [
                'search' => $search ?? '',
                'sort' => $sort ?? 'newest',
            ],
        ];
    }

    public function enrollInCourse(string $accessCode): array
    {
        $course = Course::where('access_code', $accessCode)
            ->where('is_active', true)
            ->first();

        if (! $course) {
            return [
                'success' => false,
                'error' => 'This course is not currently active.',
            ];
        }

        $user = Auth::user();

        if ($course->students()->where('user_id', $user->id)->exists()) {
            return [
                'success' => false,
                'error' => 'You are already enrolled in this course.',
            ];
        }

        $course->students()->attach($user->id, [
            'enrolled_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'Successfully enrolled in the course!',
        ];
    }

    public function getEnrolledCourse(string $courseId)
    {
        return Auth::user()->enrolledCourses()
            ->select('courses.id', 'courses.user_id', 'courses.name', 'courses.created_at')
            ->with('user:id,name')
            ->findOrFail($courseId);
    }

    public function queryActivities($course, array $params)
    {
        $page = $params['page'] ?? 1;
        $userId = Auth::id();

        return $course->activityLinks()
            ->with(['activity:id,title,language', 'activity.user:id,name'])
            ->where('is_open', true)
            ->whereDoesntHave('submissions', fn ($query) => $query->where('user_id', $userId))
            ->latest()
            ->paginate(5, ['*'], 'page', $page)
            ->withQueryString()
            ->through(fn ($link) => [
                'id' => $link->id,
                'activity_id' => $link->activity_id,
                'activity_title' => $link->activity->title ?? 'N/A',
                'activity_language' => $link->activity->language ?? 'N/A',
                'token' => $link->token,
                'is_open' => $link->is_open,
                'expires_at' => $link->expires_at,
                'created_at' => $link->created_at,
            ]);
    }

    public function queryStudents($course, array $params): array
    {
        $page = $params['page'] ?? 1;

        return $course->students()
            ->select('users.id', 'users.name', 'users.email')
            ->orderBy('users.name')
            ->paginate(10, ['*'], 'page', $page)
            ->withQueryString()
            ->through(fn ($student) => [
                'id' => $student->id,
                'name' => $student->name,
                'email' => $student->email,
            ])
            ->toArray();
    }

    public function queryCompletedActivities($course, array $params)
    {
        $page = $params['page'] ?? 1;
        $userId = Auth::id();

        return $course->activityLinks()
            ->with(['activity:id,title,language', 'activity.user:id,name'])
            ->where(function ($query) use ($userId) {
                $query->whereHas('submissions', fn ($q) => $q->where('user_id', $userId))
                    ->orWhere('is_open', false);
            })
            ->with(['submissions' => fn ($query) => $query->where('user_id', $userId)
                ->select('id', 'activity_link_id', 'score', 'created_at')
                ->latest()
                ->limit(1),
            ])
            ->latest()
            ->paginate(5, ['*'], 'page', $page)
            ->withQueryString()
            ->through(function ($link) {
                $submission = $link->submissions->first();

                return [
                    'id' => $link->id,
                    'activity_id' => $link->activity_id,
                    'activity_title' => $link->activity->title ?? 'N/A',
                    'activity_language' => $link->activity->language ?? 'N/A',
                    'token' => $link->token,
                    'is_open' => $link->is_open,
                    'submission_id' => $submission?->id,
                    'score' => $submission?->score,
                    'total_score' => $link->activity?->testCases()->sum('score'),
                    'submitted_at' => $submission?->created_at,
                ];
            });
    }
}
