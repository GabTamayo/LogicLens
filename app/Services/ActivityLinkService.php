<?php

namespace App\Services;

use App\Models\ActivityLink;
use Illuminate\Http\Request;

class ActivityLinkService
{
    public function querySubmissions(ActivityLink $link, Request $request)
    {
        $sort = $request->input('sort', 'newest');

        $query = $link->submissions()
            ->submitted()
            ->selectedAttributes()
            ->with('user:id,name,email')
            ->filterByStudent(
                $request->input('student_name'),
            );

        match ($sort) {
            'oldest' => $query->orderBy('submitted_at', 'asc'),
            'name_asc' => $query->orderByRaw('(SELECT name FROM users WHERE users.id = submissions.user_id) asc'),
            'name_desc' => $query->orderByRaw('(SELECT name FROM users WHERE users.id = submissions.user_id) desc'),
            default => $query->orderBy('submitted_at', 'desc'),
        };

        $submissions = $query->paginate(10)->withQueryString();

        $submissions->getCollection()->transform(function ($submission) {
            return [
                'id' => $submission->id,
                'student_name' => $submission->user->name,
                'student_email' => $submission->user->email,
                'code_content' => $submission->code_content,
                'language' => $submission->language,
                'score' => $submission->score,
                'total_score' => $submission->total_score, // Computed from activity test cases
                'submitted_at' => $submission->submitted_at,
            ];
        });

        return $submissions;
    }
}
