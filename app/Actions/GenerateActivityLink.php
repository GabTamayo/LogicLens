<?php

namespace App\Actions;

use App\Models\Activity;
use App\Models\ActivityLink;

class GenerateActivityLink
{
    public function execute(Activity $activity, string $courseId, ?string $expiresAt = null): ActivityLink
    {
        do {
            $token = bin2hex(random_bytes(16));
        } while (ActivityLink::where('token', $token)->exists());

        return $activity->activityLinks()->create([
            'course_id'  => $courseId,
            'token'      => $token,
            'is_open'    => true,
            'expires_at' => $expiresAt,
        ]);
    }
}
