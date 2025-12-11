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
            ->selectedAttributes()
            ->filterByStudent(
                $request->input('student_name'),
                $request->input('student_no')
            );

        match ($sort) {
            'oldest' => $query->orderBy('created_at', 'asc'),
            'name_asc' => $query->orderBy('student_name', 'asc'),
            'name_desc' => $query->orderBy('student_name', 'desc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        $submissions = $query->paginate(10)->withQueryString();

        $submissions->getCollection()
            ->transform(fn ($submission) => $submission->attachFileContent());

        return $submissions;
    }
}
