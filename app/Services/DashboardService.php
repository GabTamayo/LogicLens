<?php

namespace App\Services;

use App\Enums\ProgrammingLanguage;
use App\Models\ActivityLink;
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

    public function getUpcomingLinksThisWeek($query, int $perPage = 5)
    {
        $startOfWeek = Carbon::now('Asia/Manila')->startOfWeek();
        $endOfWeek = Carbon::now('Asia/Manila')->endOfWeek();

        return $query->where('is_open', true)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [$startOfWeek->clone()->utc(), $endOfWeek->clone()->utc()])
            ->with('activity:id,title,language')
            ->orderBy('expires_at')
            ->paginate(perPage: $perPage, pageName: 'upcoming')
            ->through(fn ($link) => [
                'id' => $link->id,
                'activity_id' => $link->activity->id,
                'activity' => $link->activity->title,
                'language' => $link->activity->language,
                'name' => $link->name,
                'expires_at' => $link->expires_at?->toDateTimeString(),
            ]);
    }

    public function getUpcomingLinksThisWeekCount($query): int
    {
        $startOfWeek = Carbon::now('Asia/Manila')->startOfWeek();
        $endOfWeek = Carbon::now('Asia/Manila')->endOfWeek();

        return $query->where('is_open', true)
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [$startOfWeek->clone()->utc(), $endOfWeek->clone()->utc()])
            ->count();
    }

    public function getFlaggedDetections($userId, int $perPage = 5)
    {
        return Detection::with([
            'activityLink:id,name,activity_id',
            'activityLink.activity:id,title',
            'submissionA:id,student_name',
            'submissionB:id,student_name',
        ])
            ->whereHas('activityLink.activity', fn ($q) => $q->where('user_id', $userId))
            ->where('flagged', true)
            ->paginate(perPage: $perPage, pageName: 'flagged')
            ->through(fn ($detection) => [
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

    public function getFlaggedDetectionsCount($userId): int
    {
        return Detection::whereHas('activityLink.activity', fn ($q) => $q->where('user_id', $userId))
            ->where('flagged', true)
            ->count();
    }

    public function getAverageScore(int|string $userId): ?float
    {
        $avg = Detection::whereHas('activityLink.activity', fn ($q) => $q->where('user_id', $userId))
            ->avg('avg_score');

        return $avg !== null ? round($avg, 2) : null;
    }

    public function getAverageScoreWithFilter(int|string $userId, ?string $filter = null): ?float
    {
        $query = Detection::whereHas('activityLink.activity', fn ($q) => $q->where('user_id', $userId));
        if ($filter === null || $filter === 'all') {
            $avg = $query->avg('avg_score');

            return $avg !== null ? round($avg, 2) : null;
        }

        if (in_array($filter, ProgrammingLanguage::getValues(), true)) {
            $query->whereHas('activityLink.activity', fn ($q) => $q->where('user_id', $userId)->where('language', $filter));
            $avg = $query->avg('avg_score');

            return $avg !== null ? round($avg, 2) : null;
        }

        $query->whereHas('activityLink', fn ($q) => $q->where('activity_id', $filter));
        $avg = $query->avg('avg_score');

        return $avg !== null ? round($avg, 2) : null;
    }

    public function getAverageScorePerActivity(int|string $userId): array
    {
        $averages = Detection::query()
            ->whereHas('activityLink.activity', fn ($q) => $q->where('user_id', $userId))
            ->with('activityLink.activity:id,title')
            ->get()
            ->groupBy(fn ($detection) => $detection->activityLink->activity->id)
            ->map(fn ($detections, $activityId) => [
                'activity_id' => $activityId,
                'activity_title' => $detections->first()->activityLink->activity->title,
                'average_score' => round($detections->avg('avg_score'), 2),
            ])
            ->values()
            ->toArray();

        return $averages;
    }

    public function getAverageScorePerActivityLink(int|string $userId, string $activityId): array
    {
        $averages = Detection::query()
            ->whereHas('activityLink.activity', fn ($q) => $q->where('user_id', $userId)->where('id', $activityId))
            ->with('activityLink:id,name,activity_id')
            ->get()
            ->groupBy(fn ($detection) => $detection->activityLink->id)
            ->map(fn ($detections, $linkId) => [
                'link_id' => $linkId,
                'link_name' => $detections->first()->activityLink->name,
                'average_score' => round($detections->avg('avg_score'), 2),
            ])
            ->values()
            ->toArray();

        return $averages;
    }

    public function getAverageScorePerActivityGroupedByLanguage(int|string $userId): array
    {
        $averages = Detection::query()
            ->whereHas('activityLink.activity', fn ($q) => $q->where('user_id', $userId))
            ->with('activityLink.activity:id,title,language')
            ->get()
            ->groupBy(fn ($detection) => $detection->activityLink->activity->language ?? 'unknown')
            ->map(fn ($detectionsByLanguage, $language) => [
                'language' => $language,
                'activities' => $detectionsByLanguage
                    ->groupBy(fn ($detection) => $detection->activityLink->activity->id)
                    ->map(fn ($detections, $activityId) => [
                        'activity_id' => $activityId,
                        'activity_title' => $detections->first()->activityLink->activity->title,
                        'average_score' => round($detections->avg('avg_score'), 2),
                    ])
                    ->values()
                    ->toArray(),
            ])
            ->toArray();

        return $averages;
    }

    public function getActiveLinks(int|string $userId)
    {
        return ActivityLink::with('activity:id,title,language')
            ->whereHas('activity', fn ($q) => $q->where('user_id', $userId))
            ->where('is_open', true)
            ->get()
            ->map(fn ($link) => [
                'id' => $link->id,
                'activity_id' => $link->activity->id,
                'activity' => $link->activity->title,
                'language' => $link->activity->language,
                'name' => $link->name,
                'expires_at' => $link->expires_at?->toDateTimeString(),
                'has_deadline' => $link->expires_at !== null,
            ]);
    }

    public function getPendingDetections(int|string $userId)
    {
        return ActivityLink::with('activity:id,title,language')
            ->whereHas('activity', fn ($q) => $q->where('user_id', $userId))
            ->where('is_open', false)
            ->doesntHave('detections')
            ->get()
            ->map(fn ($link) => [
                'id' => $link->id,
                'activity_id' => $link->activity->id,
                'activity' => $link->activity->title,
                'language' => $link->activity->language,
                'name' => $link->name,
            ]);
    }
}
