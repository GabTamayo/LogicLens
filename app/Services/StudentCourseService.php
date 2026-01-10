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
                ->select('courses.id', 'courses.user_id', 'courses.name', 'courses.cover_photo')
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
                    'cover_photo' => $course->cover_photo,
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
            ->select('courses.id', 'courses.user_id', 'courses.name', 'courses.cover_photo', 'courses.created_at')
            ->with('user:id,name')
            ->findOrFail($courseId);
    }

    public function queryActivities($course, array $params)
    {
        $page = $params['page'] ?? 1;
        $userId = Auth::id();

        return $course->activityLinks()
            ->with(['activity:id,title,language,time_limit', 'activity.user:id,name'])
            ->where('is_open', true)
            ->whereDoesntHave('submissions', fn ($query) => $query->where('user_id', $userId)->whereNotNull('submitted_at'))
            ->with(['submissions' => fn ($query) => $query->where('user_id', $userId)
                ->whereNull('submitted_at')
                ->select('id', 'activity_link_id', 'started_at', 'ending_at', 'submitted_at')
                ->limit(1),
            ])
            ->latest()
            ->paginate(5, ['*'], 'page', $page)
            ->withQueryString()
            ->through(function ($link) {
                $submission = $link->submissions->first();
                $hasTimeLimit = $link->activity && $link->activity->time_limit !== null;

                return [
                    'id' => $link->id,
                    'activity_id' => $link->activity_id,
                    'activity_title' => $link->activity->title ?? 'N/A',
                    'activity_language' => $link->activity->language ?? 'N/A',
                    'token' => $link->token,
                    'is_open' => $link->is_open,
                    'expires_at' => $link->expires_at,
                    'created_at' => $link->created_at,
                    'has_draft' => $link->submissions->isNotEmpty(),
                    'has_time_limit' => $hasTimeLimit,
                    'time_limit' => $link->activity?->time_limit,
                    'ending_at' => $submission?->ending_at?->toIso8601String(),
                    'has_timer_started' => $submission?->started_at !== null,
                ];
            });
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
                $query->whereHas('submissions', fn ($q) => $q->where('user_id', $userId)->whereNotNull('submitted_at'))
                    ->orWhere('is_open', false);
            })
            ->with(['submissions' => fn ($query) => $query->where('user_id', $userId)
                ->select('id', 'activity_link_id', 'score', 'submitted_at')
                ->latest('submitted_at')
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
                    'submitted_at' => $submission?->submitted_at,
                    'created_at' => $link->created_at,
                ];
            });
    }
}
