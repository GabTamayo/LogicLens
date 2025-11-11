<?php

namespace App\Services;

use App\Enums\ProgrammingLanguage;
use App\Models\ActivityLink;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class SubmissionService
{
    public function getSubmissionFormData(string $token): array
    {
        $activityLink = ActivityLink::with('activity')->where('token', $token)->firstOrFail();
        $language = $activityLink->activity->language;

        return [
            'bgImage' => asset('storage/images/clonewave-bg.jpg'),
            'name' => $activityLink->name,
            'activityName' => $activityLink->activity->title,
            'token' => $token,
            'allowedExtensions' => ProgrammingLanguage::fileExtensions($language),
        ];
    }

    public function storeSubmission(ActivityLink $activityLink, array $validatedData, UploadedFile $file)
    {
        $content = file_get_contents($file->getRealPath());

        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $activityTitle = str_replace([' ', '/', '\\'], '_', strtoupper($activityLink->name));
        $extension = 'txt';

        do {
            $random = bin2hex(random_bytes(8));
            $filename = "{$baseName}_{$activityTitle}_" . now()->timestamp . "{$random}.{$extension}";
            $path = "submissions/{$filename}";
        } while (Storage::disk('public')->exists($path));

        Storage::disk('public')->put($path, $content);

        $language = ProgrammingLanguage::fromFileExtension($file->getClientOriginalExtension());

        return $activityLink->submissions()->create([
            ...$validatedData,
            'file_path' => $path,
            'language'  => $language,
        ]);
    }
}
