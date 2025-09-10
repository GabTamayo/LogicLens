<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityLinkController extends Controller
{
    public function store(Request $request, Activity $activity)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $activity->activityLinks()->create([
            'name' => $request->input('name'),
            'token' => bin2hex(random_bytes(16)),
        ]);

        return redirect()->route('activities.show', $activity->id);
    }
}
