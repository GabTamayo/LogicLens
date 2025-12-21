<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CodeExecutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language' => ['required', 'string'],
            'version' => ['required', 'string'],
            'files' => ['required', 'array'],
            'files.*.content' => ['required', 'string'],
            'files.*.name' => ['nullable', 'string'],
            'stdin' => ['nullable', 'string'],
            'compile_timeout' => ['nullable', 'integer'],
            'run_timeout' => ['nullable', 'integer'],
            'compile_memory_limit' => ['nullable', 'integer'],
            'run_memory_limit' => ['nullable', 'integer'],
        ];
    }
}
