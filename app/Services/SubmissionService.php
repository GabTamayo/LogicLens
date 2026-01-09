<?php

namespace App\Services;

use App\Enums\ProgrammingLanguage;
use App\Models\ActivityLink;
use App\Models\User;

class SubmissionService
{
    public function __construct(private CodeExecutionService $codeExecutionService) {}

    public function getSubmissionFormData(ActivityLink $activityLink, User $user): array
    {
        $language = $activityLink->activity->language;

        // Create submission record if it doesn't exist
        $submission = $activityLink->submissions()
            ->firstOrCreate(
                ['user_id' => $user->id],
                ['language' => $language]
            );

        $hasSubmitted = $submission->submitted_at !== null;

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
            'draftCode' => $submission->draft_code,
            'draftStdin' => $submission->draft_stdin,
            'draftSavedAt' => $submission->draft_saved_at?->toIso8601String(),
            'testCases' => $activityLink->activity->testCases()->select('id', 'title', 'input', 'output', 'score', 'order')->get(),
        ];
    }

    public function storeSubmission(ActivityLink $activityLink, User $user, array $validatedData)
    {
        $language = $activityLink->activity->language;

        // Run test cases in backend and calculate score
        $score = $this->calculateScoreByRunningTestCases(
            $activityLink,
            $validatedData['code_content'],
            $language
        );

        // Update existing submission with submitted code and timestamp
        return $activityLink->submissions()
            ->where('user_id', $user->id)
            ->firstOrFail()
            ->update([
                'code_content' => $validatedData['code_content'],
                'score' => $score,
                'submitted_at' => now(),
            ]);
    }

    private function calculateScoreByRunningTestCases(ActivityLink $activityLink, string $code, string $language): float
    {
        $testCases = $activityLink->activity->testCases;

        if ($testCases->isEmpty()) {
            return 0;
        }

        $earnedScore = 0;
        $languageConfig = $this->getLanguageConfig($language);

        if (! $languageConfig) {
            return 0;
        }

        // Process code for Java (wrap in Main class if needed)
        $processedCode = $this->processCode($code, $language);

        foreach ($testCases as $testCase) {
            $executionResult = $this->codeExecutionService->execute([
                'language' => $languageConfig['language'],
                'version' => $languageConfig['version'],
                'files' => [
                    ['content' => $processedCode],
                ],
                'stdin' => $testCase->input,
                'compile_timeout' => 10000,
                'run_timeout' => 3000,
                'compile_memory_limit' => -1,
                'run_memory_limit' => -1,
            ]);

            // Check if execution was successful and output matches
            if ($this->isTestCasePassed($executionResult, $testCase->output)) {
                $earnedScore += $testCase->score;
            }
        }

        return $earnedScore;
    }

    private function getLanguageConfig(string $language): ?array
    {
        $languageMap = [
            'java' => ['language' => 'java', 'version' => '15.0.2'],
            'python' => ['language' => 'python', 'version' => '3.12'],
        ];

        return $languageMap[$language] ?? null;
    }

    private function processCode(string $code, string $language): string
    {
        if ($language === 'java') {
            // Check if code doesn't have a class declaration
            if (! preg_match('/^(public\s+)?class\s+\w+/m', trim($code))) {
                // Wrap the code in a Main class
                return "public class Main {\n{$code}\n}";
            }
        }

        return $code;
    }

    private function isTestCasePassed(array $executionResult, string $expectedOutput): bool
    {
        // Check for compilation errors
        if (isset($executionResult['compile']) && $executionResult['compile']['code'] !== 0) {
            return false;
        }

        // Check for runtime errors
        if (isset($executionResult['run']) && $executionResult['run']['code'] !== 0) {
            return false;
        }

        // Compare actual output with expected output
        $actualOutput = trim($executionResult['run']['stdout'] ?? '');
        $expected = trim($expectedOutput);

        return $actualOutput === $expected;
    }
}
