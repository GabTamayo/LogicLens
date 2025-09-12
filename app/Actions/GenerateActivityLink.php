<?php

namespace App\Actions;

use App\Models\Activity;
use App\Models\ActivityLink;

class GenerateActivityLink
{
    public function execute(Activity $activity, string $name): ActivityLink
    {
        do {
            $token = bin2hex(random_bytes(16));
        } while (ActivityLink::where('token', $token)->exists());

        return $activity->activityLinks()->create([
            'name'   => $name,
            'token'  => $token,
            'is_open' => true,
        ]);
    }
}
