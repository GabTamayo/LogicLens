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
            'testCases' => $activityLink->activity->testCases()->select('id', 'title', 'input', 'output', 'score', 'order')->get(),
        ];
    }

    public function storeSubmission(ActivityLink $activityLink, User $user, array $validatedData)
    {
        $language = $activityLink->activity->language;

        // Calculate score from test results
        $score = $this->calculateScore($activityLink, $validatedData['test_results'] ?? []);

        return $activityLink->submissions()->create([
            'user_id' => $user->id,
            'code_content' => $validatedData['code_content'],
            'language' => $language,
            'score' => $score,
        ]);
    }

    private function calculateScore(ActivityLink $activityLink, array $testResults): float
    {
        if (empty($testResults)) {
            return 0;
        }

        $testCases = $activityLink->activity->testCases;
        $earnedScore = 0;

        foreach ($testResults as $result) {
            if ($result['passed']) {
                $testCase = $testCases->firstWhere('id', $result['test_case_id']);
                if ($testCase) {
                    $earnedScore += $testCase->score;
                }
            }
        }

        return $earnedScore;
    }
}
