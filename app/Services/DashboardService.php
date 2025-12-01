<?php

namespace App\Services;

use App\Models\Detection;
use Carbon\Carbon;

class DashboardService
{
    public function getActiveLinksData($query): array
    {
        $activeLinks = $query->where('is_open', true);

        return [
            'total' => $activeLinks->count(),
            'noDeadline' => (clone $activeLinks)->whereNull('expires_at')->count(),
            'withDeadline' => (clone $activeLinks)->whereNotNull('expires_at')->count(),
        ];
    }

    public function getUpcomingLinksThisWeek($query)
    {
        $startOfWeek = Carbon::now('Asia/Manila')->startOfWeek();
        $endOfWeek = Carbon::now('Asia/Manila')->endOfWeek();

        return $query->where('is_open', true)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [$startOfWeek->clone()->utc(), $endOfWeek->clone()->utc()])
            ->with('activity:id,title,language')
            ->get()
            ->map(fn($link) => [
                'id' => $link->id,
                'activity_id' => $link->activity->id,
                'activity' => $link->activity->title,
                'language' => $link->activity->language,
                'name' => $link->name,
                'expires_at' => $link->expires_at?->toDateTimeString(),
            ]);
    }

    public function getFlaggedDetections($userId)
    {
        return Detection::with([
            'activityLink:id,name,activity_id',
            'activityLink.activity:id,title',
            'submissionA:id,student_name',
            'submissionB:id,student_name',
        ])
            ->whereHas('activityLink.activity', fn($q) => $q->where('user_id', $userId))
            ->where('flagged', true)
            ->get()
            ->map(fn($detection) => [
                'id' => $detection->id,
                'link_id' => $detection->activityLink->id,
                'activity_id' => $detection->activityLink->activity->id,
                'link_name' => $detection->activityLink->name,
                'activity' => $detection->activityLink->activity->title,
                'submitter_a' => $detection->submissionA?->student_name,
                'submitter_b' => $detection->submissionB?->student_name,
                'avg_score' => $detection->avg_score,
            ]);
    }

    public function getAverageScore(int|string $userId): ?float
    {
        $avg = Detection::whereHas('activityLink.activity', fn($q) => $q->where('user_id', $userId))
            ->avg('avg_score');

        return $avg !== null ? round($avg, 2) : null;
    }
}
