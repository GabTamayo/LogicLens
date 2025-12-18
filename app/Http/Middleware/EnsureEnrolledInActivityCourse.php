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
        $activityLink = ActivityLink::with('course')->where('token', $token)->firstOrFail();

        if (! $activityLink->course->students()->where('user_id', $request->user()->id)->exists()) {
            abort(403, 'You must be enrolled in this course to submit.');
        }

        $request->merge(['activityLink' => $activityLink]);

        return $next($request);
    }
}
