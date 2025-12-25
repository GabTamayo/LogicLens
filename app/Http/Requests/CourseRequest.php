<?php

namespace App\Http\Requests;

use App\Models\Course;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CourseRequest extends FormRequest
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
        $courseId = $this->route('course');
        $availableCovers = collect(Course::getAvailableCoverPhotos())->pluck('name')->implode(',');

        $uniqueRule = $courseId
            ? 'unique:courses,access_code,'.$courseId.',id'
            : 'unique:courses,access_code';

        return [
            'name' => ['required', 'string', 'max:255'],
            'access_code' => [
                'required',
                'string',
                'min:6',
                'max:12',
                $uniqueRule,
            ],
            'cover_photo' => ['nullable', 'string', 'in:'.$availableCovers],
        ];
    }
}
