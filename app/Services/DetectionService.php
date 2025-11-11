<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivityLink;
use App\Models\Detection;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DetectionService
{
    public function getDetections(Activity $activity, ActivityLink $link, Request $request): array
    {
        $filters = $request->only(['student_name_a', 'student_name_b', 'min_score']);

        return [
            'activityId' => $activity->id,
            'activityTitle' => $activity->title,
            'activityDate' => $activity->created_at->format('M d, Y'),
            'link' => [
                'id' => $link->id,
                'name' => $link->name,
            ],
            'filters' => $filters,
            'detections' => Inertia::defer(fn() => $this->queryDetections($link, $filters)),
        ];
    }

    private function queryDetections(ActivityLink $link, array $filters)
    {
        $query = Detection::forLink($link->id)
            ->filter($filters)
            ->selectedAttributes()
            ->with(['submissionA', 'submissionB'])
            ->orderByDesc('similarity_score');

        return $query->paginate(10)
            ->through(fn($detection) => $this->transformDetection($detection))
            ->withQueryString();
    }

    private function transformDetection($detection): array
    {
        return [
            'id' => $detection->id,
            'submission_a' => $this->transformSubmission($detection->submissionA),
            'submission_b' => $this->transformSubmission($detection->submissionB),
            'similarity_score' => $detection->similarity_score,
            'created_at' => $detection->created_at,
        ];
    }

    private function transformSubmission($submission): ?array
    {
        if (!$submission) return null;

        return [
            'id' => $submission->id,
            'student_name' => $submission->student_name,
            'student_no' => $submission->student_no,
            'student_email' => $submission->student_email,
        ];
    }
}
