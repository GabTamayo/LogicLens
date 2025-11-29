<?php

namespace App\Http\Controllers;

use App\Models\ActivityLink;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();
        $now = Carbon::now();

        // Scope links to the current user's activities only
        $userLinksQuery = ActivityLink::whereHas('activity', fn($q) => $q->where('user_id', $userId));

        $activeLinksData = [
            'total' => (clone $userLinksQuery)->where('is_open', true)->count(),
            'noDeadline' => (clone $userLinksQuery)->where('is_open', true)->whereNull('expires_at')->count(),
            'withDeadline' => (clone $userLinksQuery)->where('is_open', true)->whereNotNull('expires_at')->count()
        ];

        return Inertia::render('Dashboard', [
            'activeLinksData' => $activeLinksData,
        ]);
    }

    public function activeLinks()
    {
        return Inertia::render('Dashboard/ActiveLinks');
    }
}
