<?php

namespace App\Http\Requests\TypeEntite;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTypeEntiteRequest extends FormRequest
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
            'libelle' => [
                'required',
                'string',
                'max:255',
                'unique:type_entites,libelle'
            ],
            'code' => [
                'nullable',
                'uuid',
                'unique:type_entites,code'
            ]
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'libelle.required' => 'Le libellé est obligatoire.',
            'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
            'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
            'libelle.unique' => 'Ce libellé existe déjà.',
            'code.uuid' => 'Le code doit être un UUID valide.',
            'code.unique' => 'Ce code existe déjà.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'libelle' => 'libellé',
            'code' => 'code'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Générer automatiquement un UUID si le code n'est pas fourni
        if (!$this->has('code') || empty($this->code)) {
            $this->merge([
                'code' => \Illuminate\Support\Str::uuid()->toString()
            ]);
        }
    }
}