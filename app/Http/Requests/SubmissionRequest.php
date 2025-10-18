<?php

namespace App\Http\Requests;

use App\Models\ActivityLink;
use App\Models\Submission;
use Illuminate\Foundation\Http\FormRequest;

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
            'student_name' => ['required', 'string', 'max:255'],
            'student_email' => ['required', 'email', 'max:255'],
            'student_no'    => ['required', 'string', 'max:50'],
            'code_file'          => ['required', 'file', 'mimetypes:text/plain,text/x-java-source,application/octet-stream', 'max:10240'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $activityLink = ActivityLink::where('token', $this->route('token'))->first();

            if (! $activityLink) {
                return;
            }

            $fields = [
                'student_email' => 'email',
                'student_no' => 'student number',
            ];

            foreach ($fields as $field => $label) {
                $value = $this->input($field);

                if (Submission::where('activity_link_id', $activityLink->id)->where($field, $value)->exists()) {
                    $validator->errors()->add($field, "This {$label} has already submitted for this activity.");
                }
            }
        });
    }
}
