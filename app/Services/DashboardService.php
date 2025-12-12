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
            ->with(['activity:id,title,language', 'course:id,name'])
            ->orderBy('expires_at')
            ->paginate(perPage: $perPage, pageName: 'upcoming')
            ->through(fn ($link) => [
                'id' => $link->id,
                'activity_id' => $link->activity->id,
                'activity' => $link->activity->title,
                'language' => $link->activity->language,
                'course_id' => $link->course_id,
                'course' => $link->course ? [
                    'id' => $link->course->id,
                    'name' => $link->course->name,
                ] : null,
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
            'activityLink:id,course_id,activity_id',
            'activityLink.activity:id,title',
            'activityLink.course:id,name',
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
                'course_id' => $detection->activityLink->course_id,
                'course' => $detection->activityLink->course ? [
                    'id' => $detection->activityLink->course->id,
                    'name' => $detection->activityLink->course->name,
                ] : null,
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
        return Detection::query()
            ->join('activity_links', 'detections.activity_link_id', '=', 'activity_links.id')
            ->join('activities', 'activity_links.activity_id', '=', 'activities.id')
            ->where('activities.user_id', $userId)
            ->selectRaw('
                activities.id as activity_id,
                activities.title as activity_title,
                COALESCE(activities.language, \'unknown\') as language,
                ROUND(AVG(detections.avg_score), 2) as average_score
            ')
            ->groupBy('activities.id', 'activities.title', 'activities.language')
            ->orderBy('activities.title')
            ->get()
            ->map(fn ($item) => [
                'activity_id' => $item->activity_id,
                'activity_title' => $item->activity_title,
                'language' => $item->language,
                'average_score' => (float) $item->average_score,
            ])
            ->values()
            ->toArray();
    }

    public function getAverageScorePerActivityLink(int|string $userId, string $activityId): array
    {
        $averages = Detection::query()
            ->whereHas('activityLink.activity', fn ($q) => $q->where('user_id', $userId)->where('id', $activityId))
            ->with('activityLink:id,course_id,activity_id', 'activityLink.course:id,name')
            ->get()
            ->groupBy(fn ($detection) => $detection->activityLink->id)
            ->map(fn ($detections, $linkId) => [
                'link_id' => $linkId,
                'course' => $detections->first()->activityLink->course ? [
                    'id' => $detections->first()->activityLink->course->id,
                    'name' => $detections->first()->activityLink->course->name,
                ] : null,
                'average_score' => round($detections->avg('avg_score'), 2),
            ])
            ->values()
            ->toArray();

        return $averages;
    }

    public function getActiveLinks(int|string $userId)
    {
        return ActivityLink::with('activity:id,title,language', 'course:id,name')
            ->whereHas('activity', fn ($q) => $q->where('user_id', $userId))
            ->where('is_open', true)
            ->get()
            ->map(function ($link) {
                $hasDeadline = $link->expires_at !== null;

                return [
                    'id' => $link->id,
                    'activity_id' => $link->activity->id,
                    'activity' => $link->activity->title,
                    'language' => $link->activity->language,
                    'course_id' => $link->course_id,
                    'course' => $link->course ? [
                        'id' => $link->course->id,
                        'name' => $link->course->name,
                    ] : null,
                    'expires_at' => $link->expires_at?->toDateTimeString(),
                    'has_deadline' => $hasDeadline,
                    'created_at' => $link->created_at?->toDateTimeString(),
                ];
            });
    }

    public function getPendingDetections(int|string $userId)
    {
        return ActivityLink::with('activity:id,title,language', 'course:id,name')
            ->whereHas('activity', fn ($q) => $q->where('user_id', $userId))
            ->where('is_open', false)
            ->doesntHave('detections')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($link) => [
                'id' => $link->id,
                'activity_id' => $link->activity->id,
                'activity' => $link->activity->title,
                'language' => $link->activity->language,
                'course_id' => $link->course_id,
                'course' => $link->course ? [
                    'id' => $link->course->id,
                    'name' => $link->course->name,
                ] : null,
                'created_at' => $link->created_at?->toDateTimeString(),
            ]);
    }
}
