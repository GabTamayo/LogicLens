<?php

namespace App\Http\Middleware;

use App\Models\ActivityLink;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEnrolledInActivityCourse
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->route('token');
        $activityLink = ActivityLink::with(['course', 'activity'])->where('token', $token)->firstOrFail();

        $user = $request->user();

        $isTeacherOwner = $activityLink->activity->user_id === $user->id;
        $isEnrolledStudent = $activityLink->course->students()->where('user_id', $user->id)->exists();

        if (! $isTeacherOwner && ! $isEnrolledStudent) {
            abort(403, 'You must be enrolled in this course or be the activity creator to access this page.');
        }

        $request->merge(['activityLink' => $activityLink]);

        return $next($request);
    }
}
