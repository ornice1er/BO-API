<?php

namespace App\Http\Requests\EntiteAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEntiteAdminRequest extends FormRequest
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
        $entiteAdminId = $this->route('entity'); // ou $this->route('id') selon votre route

        return [
            'libelle' => [
                'required',
                'string',
                'max:255',
                Rule::unique('entite_admins', 'libelle')->ignore($entiteAdminId)
            ],
            'sigle' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('entite_admins', 'sigle')->ignore($entiteAdminId)
            ],
            'type_entity_id' => [
                'required',
                'integer',
                'exists:type_entites,id'
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
            'code.uuid' => 'Le code doit être un UUID valide.',
            'code.unique' => 'Ce code existe déjà.',

            'libelle.required' => 'Le libellé est obligatoire.',
            'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
            'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
            'libelle.unique' => 'Ce libellé existe déjà.',

            'sigle.string' => 'Le sigle doit être une chaîne de caractères.',
            'sigle.max' => 'Le sigle ne peut pas dépasser 20 caractères.',
            'sigle.unique' => 'Ce sigle existe déjà.',

            'type_entity_id.required' => 'Le type d\'entité est obligatoire.',
            'type_entity_id.integer' => 'Le type d\'entité doit être un nombre entier.',
            'type_entity_id.exists' => 'Le type d\'entité sélectionné n\'existe pas.'
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
            'code' => 'code',
            'libelle' => 'libellé',
            'sigle' => 'sigle',
            'type_entity_id' => 'type d\'entité'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Nettoyer et formater les données
        $this->merge([
            'libelle' => $this->libelle ? trim($this->libelle) : null,
            'sigle' => $this->sigle ? strtoupper(trim($this->sigle)) : null,
        ]);
    }
}
