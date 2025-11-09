<?php

namespace App\Http\Requests;

use App\Enums\ProgrammingLanguage;
use App\Models\ActivityLink;
use App\Models\Submission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class SubmissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_name'  => ['required', 'string', 'max:255'],
            'student_email' => ['required', 'email', 'max:255'],
            'student_no'    => ['required', 'string', 'max:50'],
            'code_file'     => ['required', 'file', 'max:10240'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $activityLink = ActivityLink::where('token', $this->route('token'))->with('activity')->first();
            if (! $activityLink) return;

            $fields = [
                'student_email' => 'email',
                'student_no' => 'student number',
            ];
            foreach ($fields as $field => $label) {
                $value = $this->input($field);
                if (Submission::where('activity_link_id', $activityLink->id)
                    ->where($field, $value)
                    ->exists()
                ) {
                    $validator->errors()->add($field, "This {$label} has already submitted for this activity.");
                }
            }

            $file = $this->file('code_file');
            $language = $activityLink->activity->language ?? null;
            if ($file && $language) {
                $allowedExtensions = ProgrammingLanguage::fileExtensions($language);

                $ext = strtolower($file->getClientOriginalExtension());
                if (! in_array($ext, $allowedExtensions)) {
                    $validator->errors()->add(
                        'code_file',
                        'The code file must be a valid ' . ProgrammingLanguage::response($language) . ' file.'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'code_file.required' => 'Please upload a code file.',
            'code_file.file' => 'The uploaded file must be a valid file.',
            'code_file.max' => 'The code file must not be greater than 10MB.',
        ];
    }
}
