<?php

namespace App\Http\Requests;

use App\Enums\ProgrammingLanguage;
use BenSampo\Enum\Rules\EnumValue;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class ActivityRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:100'],
            'language' => ['required', new EnumValue(ProgrammingLanguage::class)],
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
            'title.required' => 'Please enter an activity title.',
            'title.max' => 'The activity title must not exceed 100 characters.',
            'language.required' => 'Please select a programming language.',
            'test_cases.*.title.required' => 'Please enter a title for this test case.',
            'test_cases.*.title.max' => 'The test case title must not exceed 255 characters.',
            'test_cases.*.output.required' => 'Please enter the expected output for this test case.',
            'test_cases.*.score.required' => 'Please enter a score for this test case.',
            'test_cases.*.score.numeric' => 'The score must be a valid number.',
            'test_cases.*.score.min' => 'The score must be at least 0.',
        ];
    }
}
