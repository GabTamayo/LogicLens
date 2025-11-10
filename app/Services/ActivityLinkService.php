<?php

namespace App\Services;

use App\Models\ActivityLink;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLinkService
{
    public function getSubmissions(ActivityLink $link, Request $request): array
    {
        return [
            'activityId' => $link->activity_id,
            'activityTitle' => $link->activity->title,
            'link' => $link,
            'filters' => $request->only(['student_name', 'student_no']),
            'submissions' => Inertia::defer(function () use ($link, $request) {
                $submissions = $link->submissions()
                    ->selectedAttributes()
                    ->filterByStudent(
                        $request->input('student_name'),
                        $request->input('student_no')
                    )
                    ->orderBy('created_at')
                    ->paginate(10)
                    ->withQueryString();

                // Attach file content to each submission
                $submissions->getCollection()
                    ->transform(fn($submission) => $submission->attachFileContent());

                return $submissions;
            }),
        ];
    }
}
