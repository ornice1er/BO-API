<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class AddRequestsToProjectRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'request_ids' => 'required|array',
            'request_ids.*' => 'required|integer|exists:requetes,id',
        ];
    }

    public function messages(): array
    {
        return [
            'request_ids.required' => 'Request IDs list is required',
            'request_ids.array' => 'Request IDs must be an array',
            'request_ids.*.exists' => 'One or more request IDs do not exist',
        ];
    }
}
