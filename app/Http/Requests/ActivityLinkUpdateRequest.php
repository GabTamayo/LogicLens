<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ActivityLinkUpdateRequest extends FormRequest
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
        return [
            'is_open'    => ['required', 'boolean'],
            'expires_at' => ['nullable', 'date', 'after_or_equal:now'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Get the link from the route
            $linkId = $this->route('link') ?? $this->route()->parameter('link');
            $activity = $this->route('activity');

            if ($activity && $linkId) {
                $link = $activity->activityLinks()->find($linkId);

                // Warn if trying to open a link with a past expiration date
                // without updating the expiration date
                if (
                    $link &&
                    $this->input('is_open') === true &&
                    $link->expires_at &&
                    $link->expires_at->isPast() &&
                    (!$this->has('expires_at') || $this->input('expires_at') === $link->expires_at->format('Y-m-d'))
                ) {
                    $validator->errors()->add(
                        'expires_at',
                        'This link has reached the deadline. Please update the deadline to a future date or remove it.'
                    );
                }
            }
        });
    }

    public function messages()
    {
        return [
            'expires_at.date' => 'The expiration date must be a valid date.',
            'expires_at.after_or_equal' => 'The expiration date cannot be earlier than today.',
        ];
    }
}
