<?php

namespace App\Services;

use App\Models\ActivityLink;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ActivityLinkService
{
    public function querySubmissions(ActivityLink $link, Request $request)
    {
        $submissions = $link->submissions()
            ->selectedAttributes()
            ->filterByStudent(
                $request->input('student_name'),
                $request->input('student_no')
            )
            ->orderBy('created_at')
            ->paginate(10)
            ->withQueryString();

        $submissions->getCollection()
            ->transform(fn($submission) => $submission->attachFileContent());

        return $submissions;
    }
}
