<?php

namespace App\Services;

use App\Models\ActivityLink;
use App\Models\Submission;
use Illuminate\Support\Facades\Auth;

class SubmissionDraftService
{
    public function saveDraft(string $token, array $data): void
    {
        $activityLink = ActivityLink::where('token', $token)->firstOrFail();

        $submission = Submission::where('activity_link_id', $activityLink->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $submission->update([
            'draft_code' => $data['code'] ?? null,
            'draft_stdin' => $data['stdin'] ?? null,
            'draft_saved_at' => now(),
        ]);
    }
}
