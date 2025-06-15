<?php

namespace App\Http\Requests\Export;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ExportRequest extends FormRequest
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
            'title' => 'required|string',
            'subtitle' => 'nullable|string',
            'description' => 'nullable|string',
            'table_header' => 'nullable',
            'table_body' => 'nullable',
            'content' => 'nullable|string',
            'conclusion' => 'nullable|string',
        ];
    }

    /**
     * Gestion des erreurs de validation
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    /**
     * Messages d'erreur en Français
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.string' => 'Le titre doit être une chaîne de caractères.',
            'title.required' => 'Le titre est requis.',
            'content.required' => 'Le contenu est requis.',
            'content.string' => 'Le contenu doit être une chaîne de caractères.',
            'conclusion.required' => 'La conclusion est requise.',
            'conclusion.string' => 'La conclusion doit être une chaîne de caractères.',
        ];
    }
}
