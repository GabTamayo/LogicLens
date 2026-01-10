<?php

namespace App\Http\Controllers;

use App\Models\ActivityLink;
use App\Models\ExamViolation;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExamViolationController extends Controller
{
    public function store(Request $request, string $token)
    {
        $validated = $request->validate([
            'violation_type' => ['required', 'string', 'in:tab_switch,fullscreen_exit,keyboard_shortcut,context_menu,window_blur'],
            'details' => ['nullable', 'string', 'max:1000'],
            'timestamp' => ['required', 'date'],
        ]);

        $activityLink = ActivityLink::where('token', $token)->firstOrFail();

        $submission = Submission::where('activity_link_id', $activityLink->id)
            ->where('user_id', Auth::id())
            ->first();

        ExamViolation::create([
            'submission_id' => $submission?->id,
            'token' => $token,
            'violation_type' => $validated['violation_type'],
            'details' => $validated['details'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'violated_at' => $validated['timestamp'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Violation logged successfully',
        ]);
    }
}
