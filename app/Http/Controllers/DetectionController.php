<?php

namespace App\Http\Controllers;

use App\Enums\ProgrammingLanguage;
use App\Models\Activity;
use App\Models\ActivityLink;
use App\Models\Detection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DetectionController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    public function detect(Activity $activity, $linkId)
    {
        $activityLink = ActivityLink::with('activity')->findOrFail($linkId);

        $submissions = $activityLink->submissions()->get();

        if ($submissions->count() < 2) {
            return back()->with('error', 'Need at least 2 submissions to detect plagiarism');
        }

        // Get language from first submission (all should be same language)
        $language = $submissions->first()->language;

        $supportedLanguages = ProgrammingLanguage::getValues();
        if (!in_array($language, $supportedLanguages)) {
            return back()->with('error', "Unsupported language: {$language}");
        }

        // Map submissions with file content
        $submissionsData = $submissions->map(function ($submission) {
            if ($submission->file_path && Storage::disk('public')->exists($submission->file_path)) {
                return [
                    'id' => $submission->id,
                    'file_content' => Storage::disk('public')->get($submission->file_path),
                ];
            }
            return null;
        })->filter()->values();

        try {
            $fastApiUrl = config('services.fastapi.url', 'http://localhost:8001');

            $response = Http::timeout(120)->post("{$fastApiUrl}/detect", [
                'submissions' => $submissionsData->toArray(),
                'language' => $language,
            ]);

            if ($response->successful()) {
                $results = $response->json()['results'];

                // Clear previous detections for this activity link
                Detection::where('activity_link_id', $linkId)->delete();

                // Store new results
                foreach ($results as $result) {
                    Detection::create([
                        'activity_link_id' => $linkId,
                        'submission_a_id' => $result['submission_a_id'],
                        'submission_b_id' => $result['submission_b_id'],
                        'similarity_score' => $result['similarity_score'],
                    ]);
                }

                $count = count($results);

                if ($count === 0) {
                    return redirect()
                        ->route('activities.links.show', ['activity' => $activity->id, 'link' => $linkId])
                        ->with('info', 'No similar submissions found (all scores below 50%).');
                }

                return redirect()
                    ->route('activities.links.show', ['activity' => $activity->id, 'link' => $linkId])
                    ->with('success', "Detection complete! Found {$count} similar pairs.");
            }

            return back()->with('error', 'Detection failed: ' . $response->body());
        } catch (\Exception $e) {
            return back()->with('error', 'Detection error: ' . $e->getMessage());
        }
    }

    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Detection $detection)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Detection $detection)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Detection $detection)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Detection $detection)
    {
        //
    }
}
