<?php

namespace App\Http\Controllers;

use App\Models\ActivityLink;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(DashboardService $dashboardService)
    {
        $userId = Auth::id();

        $activeLinksQuery = ActivityLink::whereHas('activity', fn ($q) => $q->where('user_id', $userId));

        return Inertia::render('Dashboard', [
            'totalActivityLinks' => (clone $activeLinksQuery)->count(),
            'activeLinksData' => $dashboardService->getActiveLinksData($activeLinksQuery),
            'totalLinksWithoutDetections' => (clone $activeLinksQuery)->doesntHave('detections')->count(),
            'totalUpcomingThisWeek' => $dashboardService->getUpcomingLinksThisWeek($activeLinksQuery)->count(),
            'totalFlaggedDetections' => $dashboardService->getFlaggedDetections($userId)->count(),
            'totalAverageScore' => $dashboardService->getAverageScore($userId),
            'upcomingThisWeek' => Inertia::defer(fn () => $dashboardService->getUpcomingLinksThisWeek($activeLinksQuery)),
            'flaggedDetections' => Inertia::defer(fn () => $dashboardService->getFlaggedDetections($userId)),
        ]);
    }

    public function activeLinks()
    {
        return Inertia::render('Dashboard/ActiveLinks');
    }
}
