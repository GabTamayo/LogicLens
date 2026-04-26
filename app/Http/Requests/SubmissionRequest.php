<?php

namespace App\Http\Requests;

use App\Models\ActivityLink;
use App\Models\Submission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SubmissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'code_content' => ['required', 'string'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $activityLink = ActivityLink::where('token', $this->route('token'))->first();

            if (! $activityLink) {
                return;
            }

            $this->validateActivityLinkIsOpen($validator, $activityLink);
            $this->validateUniqueSubmission($validator, $activityLink);
        });
    }

    private function validateUniqueSubmission($validator, ActivityLink $activityLink): void
    {
        if (Submission::where('activity_link_id', $activityLink->id)
            ->where('user_id', Auth::id())
            ->whereNotNull('submitted_at')
            ->exists()
        ) {
            $validator->errors()->add(
                'code_content',
                'You have already submitted for this activity.'
            );
        }
    }

    private function validateActivityLinkIsOpen($validator, ActivityLink $activityLink): void
    {
        if (! $activityLink->is_open) {
            $validator->errors()->add(
                'code_content',
                'This activity link is closed and no longer accepting submissions.'
            );
        }
    }

    public function messages(): array
    {
        return [
            'code_content.required' => 'Please write your code.',
            'code_content.string' => 'The code must be valid text.',
            'code_content.min' => 'The code must be at least 10 characters.',
        ];
    }
}
