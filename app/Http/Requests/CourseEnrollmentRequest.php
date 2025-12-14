<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CourseEnrollmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'access_code' => ['required', 'string', 'exists:courses,access_code'],
        ];
    }

    public function messages(): array
    {
        return [
            'access_code.required' => 'Please enter an access code.',
            'access_code.exists' => 'Invalid access code. Please check and try again.',
        ];
    }
}
