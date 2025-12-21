<?php

namespace App\Services;

use App\Models\ActivityLink;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;

class SubmissionDraftService
{
    public function saveDraft(string $token, array $data): array
    {
        $activityLink = ActivityLink::where('token', $token)->firstOrFail();

        $submission = Submission::firstOrCreate(
            [
                'activity_link_id' => $activityLink->id,
                'user_id' => Auth::id(),
            ],
            [
                'code_content' => '',
                'language' => $activityLink->activity->language ?? 'java',
            ]
        );

        $submission->update([
            'draft_code' => $data['code'] ?? null,
            'draft_stdin' => $data['stdin'] ?? null,
            'draft_saved_at' => now(),
        ]);

        return [
            'success' => true,
            'saved_at' => $submission->draft_saved_at->toIso8601String(),
        ];
    }

    public function getDraft(string $token): array
    {
        $activityLink = ActivityLink::where('token', $token)->firstOrFail();

        $submission = Submission::where('activity_link_id', $activityLink->id)
            ->where('user_id', Auth::id())
            ->first();

        if (! $submission || ! $submission->draft_code) {
            return [
                'has_draft' => false,
                'draft' => null,
            ];
        }

        return [
            'has_draft' => true,
            'draft' => [
                'code' => $submission->draft_code,
                'stdin' => $submission->draft_stdin,
                'saved_at' => $submission->draft_saved_at?->toIso8601String(),
            ],
        ];
    }
}
