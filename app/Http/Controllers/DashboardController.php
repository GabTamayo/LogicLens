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

        $baseQuery = ActivityLink::whereHas('activity', fn ($q) => $q->where('user_id', $userId));

        return Inertia::render('Dashboard', [
            'totalActivityLinks' => (clone $baseQuery)->count(),
            'activeLinksData' => $dashboardService->getActiveLinksData(clone $baseQuery),
            'totalLinksWithoutDetections' => (clone $baseQuery)->where('is_open', false)->doesntHave('detections')->count(),
            'totalUpcomingThisWeek' => $dashboardService->getUpcomingLinksThisWeekCount(clone $baseQuery),
            'totalFlaggedDetections' => $dashboardService->getFlaggedDetectionsCount($userId),
            'totalAverageScore' => $dashboardService->getAverageScore($userId),
            'averageScorePerActivity' => $dashboardService->getAverageScorePerActivity($userId),
            'upcomingThisWeek' => Inertia::scroll(fn () => $dashboardService->getUpcomingLinksThisWeek(clone $baseQuery)),
            'flaggedDetections' => Inertia::scroll(fn () => $dashboardService->getFlaggedDetections($userId)),
        ]);
    }

    public function activeLinks(DashboardService $dashboardService)
    {
        return Inertia::modal('Dashboard/ActiveLinks', [
            'activeLinks' => Inertia::defer(fn () => $dashboardService->getActiveLinks(Auth::id())),
        ]);
    }

    public function pendingDetections(DashboardService $dashboardService)
    {
        return Inertia::modal('Dashboard/PendingDetections', [
            'pendingDetections' => Inertia::defer(fn () => $dashboardService->getPendingDetections(Auth::id())),
        ]);
    }

    public function getAverageScorePerActivityLink(DashboardService $dashboardService, string $activityId)
    {
        $userId = Auth::id();

        return response()->json([
            'data' => $dashboardService->getAverageScorePerActivityLink($userId, $activityId),
        ]);
    }

    public function getAverageScore(DashboardService $dashboardService)
    {
        $userId = Auth::id();
        $filter = request()->query('filter', 'all');

        return response()->json([
            'averageScore' => $dashboardService->getAverageScoreWithFilter($userId, $filter),
        ]);
    }
}
