<?php

namespace App\Services;

use App\Enums\ProgrammingLanguage;
use App\Models\ActivityLink;
use App\Models\User;

class SubmissionService
{
    public function getSubmissionFormData(ActivityLink $activityLink, User $user): array
    {
        $language = $activityLink->activity->language;

        return [
            'bgImage' => asset('images/clonewave-bg.jpg'),
            'courseName' => $activityLink->course->name,
            'activityName' => $activityLink->activity->title,
            'activityContent' => $activityLink->activity->content,
            'token' => $activityLink->token,
            'language' => $language,
            'languageText' => ProgrammingLanguage::response($language),
            'studentName' => $user->name,
            'studentEmail' => $user->email,
        ];
    }

    public function storeSubmission(ActivityLink $activityLink, User $user, array $validatedData)
    {
        $language = $activityLink->activity->language;

        return $activityLink->submissions()->create([
            'user_id' => $user->id,
            'code_content' => $validatedData['code_content'],
            'language' => $language,
        ]);
    }
}
