<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ActivityLinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $activityId = $this->route('activity')?->id;

        return [
            'course_id'  => [
                'required',
                'uuid',
                'exists:courses,id',
                function ($attribute, $value, $fail) use ($activityId) {
                    $exists = \App\Models\ActivityLink::where('activity_id', $activityId)
                        ->where('course_id', $value)
                        ->exists();

                    if ($exists) {
                        $fail('An activity link for this course already exists.');
                    }
                },
            ],
            'expires_at' => ['nullable', 'date', 'after_or_equal:now'],
        ];
    }

    public function messages()
    {
        return [
            'expires_at.date' => 'The expiration date must be a valid date.',
            'expires_at.after_or_equal' => 'The expiration date cannot be earlier than today.',
        ];
    }
}
