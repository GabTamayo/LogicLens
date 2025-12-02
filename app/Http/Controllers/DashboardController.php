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

        $activeLinksQuery = ActivityLink::whereHas('activity', fn($q) => $q->where('user_id', $userId));

        return Inertia::render('Dashboard', [
            'totalActivityLinks' => (clone $activeLinksQuery)->count(),
            'activeLinksData' => $dashboardService->getActiveLinksData(clone $activeLinksQuery),
            'totalLinksWithoutDetections' => (clone $activeLinksQuery)->where('is_open', false)->doesntHave('detections')->count(),
            'totalUpcomingThisWeek' => $dashboardService->getUpcomingLinksThisWeek(clone $activeLinksQuery)->count(),
            'totalFlaggedDetections' => $dashboardService->getFlaggedDetections($userId)->count(),
            'totalAverageScore' => $dashboardService->getAverageScore($userId),
            'averageScorePerActivity' => $dashboardService->getAverageScorePerActivity($userId),
            'averageScorePerActivityGroupedByLanguage' => $dashboardService->getAverageScorePerActivityGroupedByLanguage($userId),
            'upcomingThisWeek' => Inertia::defer(fn() => $dashboardService->getUpcomingLinksThisWeek(clone $activeLinksQuery)),
            'flaggedDetections' => Inertia::defer(fn() => $dashboardService->getFlaggedDetections($userId)),
        ]);
    }

    public function activeLinks(DashboardService $dashboardService)
    {
        return Inertia::modal('Dashboard/ActiveLinks', [
            'activeLinks' => Inertia::defer(fn() => $dashboardService->getActiveLinks(Auth::id())),
        ]);
    }

    public function pendingDetections(DashboardService $dashboardService)
    {
        return Inertia::modal('Dashboard/PendingDetections', [
            'pendingDetections' => Inertia::defer(fn() => $dashboardService->getPendingDetections(Auth::id())),
        ]);
    }

    public function getAverageScorePerActivityLink(DashboardService $dashboardService, string $activityId)
    {
        $userId = Auth::id();

        return response()->json([
            'data' => $dashboardService->getAverageScorePerActivityLink($userId, $activityId),
        ]);
    }
}
