<?php

namespace App\Actions;

use App\Models\Submission;

class OneTimeSubmission
{
    public function alreadySubmitted(int $activityLinkId, string $studentEmail): bool
    {
        return Submission::where('activity_link_id', $activityLinkId)
            ->where('student_email', $studentEmail)
            ->exists();
    }
}
