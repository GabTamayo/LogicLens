<?php

namespace App\Http\Controllers;

use App\Actions\GenerateActivityLink;
use App\Models\Activity;
use Illuminate\Http\Request;

class ActivityLinkController extends Controller
{
    public function store(Request $request, Activity $activity, GenerateActivityLink $generateActivityLink)
    {
        $request->validate([
            'name' => 'required|string|max:100',
        ]);

        $generateActivityLink->execute($activity, $request->input('name'));

        return redirect()->route('activities.show', $activity);
    }

    public function update(Request $request, Activity $activity, $link)
    {
        $link = $activity->activityLinks()->findOrFail($link);

        $switch = $request->validate([
            'is_open' => 'required|boolean',
        ]);

        $link->update($switch);

        return back()->with('success', 'Status updated successfully!');
    }
}
