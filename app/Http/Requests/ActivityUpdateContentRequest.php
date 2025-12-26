<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ActivityUpdateContentRequest extends FormRequest
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
            'content' => ['nullable', 'string'],
            'test_cases' => ['nullable', 'array'],
            'test_cases.*.title' => ['required', 'string', 'max:255'],
            'test_cases.*.input' => ['nullable', 'string'],
            'test_cases.*.output' => ['required', 'string'],
            'test_cases.*.score' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.string' => 'The activity content must be a valid text.',
            'test_cases.array' => 'The test cases must be a valid array.',
            'test_cases.*.title.required' => 'Each test case must have a title.',
            'test_cases.*.title.string' => 'The test case title must be a valid text.',
            'test_cases.*.title.max' => 'The test case title must not exceed 255 characters.',
            'test_cases.*.input.string' => 'The test case input must be a valid text.',
            'test_cases.*.output.required' => 'Each test case must have an expected output.',
            'test_cases.*.output.string' => 'The test case output must be a valid text.',
            'test_cases.*.score.required' => 'Each test case must have a score.',
            'test_cases.*.score.numeric' => 'The test case score must be a number.',
            'test_cases.*.score.min' => 'The test case score must be at least 0.',
        ];
    }
}
