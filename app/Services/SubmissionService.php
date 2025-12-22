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
        $hasSubmitted = $activityLink->submissions()->where('user_id', $user->id)->exists();

        return [
            'bgImage' => asset('images/clonewave-bg.jpg'),
            'courseName' => $activityLink->course->name,
            'courseId' => $activityLink->course->id,
            'activityId' => $activityLink->activity->id,
            'activityName' => $activityLink->activity->title,
            'activityContent' => $activityLink->activity->content,
            'token' => $activityLink->token,
            'language' => $language,
            'languageText' => ProgrammingLanguage::response($language),
            'studentName' => $user->name,
            'studentEmail' => $user->email,
            'hasSubmitted' => $hasSubmitted,
            'testCases' => $activityLink->activity->testCases()->select('id', 'title', 'input', 'output', 'order')->get(),
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
