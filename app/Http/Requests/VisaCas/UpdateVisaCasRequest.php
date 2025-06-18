<?php

namespace App\Http\Requests\VisaCas;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVisaCasRequest extends FormRequest
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
        $visaCasId = $this->route('visa_ca'); // ou $this->route('id') selon votre route

        return [
            'code' => [
                'nullable',
                'uuid',
                Rule::unique('visa_c_a_s', 'code')->ignore($visaCasId)
            ],
            'name_structure' => [
                'required',
                'string',
                'max:255'
            ],
            'rccm' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[A-Z]{2}-[A-Z]{3}-\d{4}-[A-Z]-\d{5}$|^[A-Z]{2}-[A-Z]{3}-\d{4}-[A-Z]\d{5}$|^[A-Z]{2}[A-Z]{3}\d{4}[A-Z]\d{5}$/',
                Rule::unique('visa_c_a_s', 'rccm')->ignore($visaCasId)
            ],
            'ifu' => [
                'nullable',
                'string',
                'size:13',
                'regex:/^\d{13}$/',
                Rule::unique('visa_c_a_s', 'ifu')->ignore($visaCasId)
            ],
            'message' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'requete_id' => [
                'required',
                'integer',
                'exists:requetes,id'
            ],
            'hasDelegated' => [
                'nullable',
                'boolean'
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

            'name_structure.required' => 'Le nom de la structure est obligatoire.',
            'name_structure.string' => 'Le nom de la structure doit être une chaîne de caractères.',
            'name_structure.max' => 'Le nom de la structure ne peut pas dépasser 255 caractères.',

            'rccm.string' => 'Le RCCM doit être une chaîne de caractères.',
            'rccm.max' => 'Le RCCM ne peut pas dépasser 50 caractères.',
            'rccm.regex' => 'Le format du RCCM n\'est pas valide. Format attendu: BJ-COT-2021-A-12345',
            'rccm.unique' => 'Ce numéro RCCM existe déjà.',

            'ifu.string' => 'L\'IFU doit être une chaîne de caractères.',
            'ifu.size' => 'L\'IFU doit contenir exactement 13 chiffres.',
            'ifu.regex' => 'L\'IFU doit contenir uniquement des chiffres (13 chiffres).',
            'ifu.unique' => 'Ce numéro IFU existe déjà.',

            'message.string' => 'Le message doit être une chaîne de caractères.',
            'message.max' => 'Le message ne peut pas dépasser 1000 caractères.',

            'requete_id.required' => 'La requête est obligatoire.',
            'requete_id.integer' => 'L\'identifiant de la requête doit être un nombre entier.',
            'requete_id.exists' => 'La requête sélectionnée n\'existe pas.',

            'hasDelegated.boolean' => 'Le champ délégation doit être vrai ou faux.'
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
            'name_structure' => 'nom de la structure',
            'rccm' => 'RCCM',
            'ifu' => 'IFU',
            'message' => 'message',
            'requete_id' => 'requête',
            'hasDelegated' => 'délégation'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Nettoyer et formater les données
        $this->merge([
            'name_structure' => $this->name_structure ? trim($this->name_structure) : null,
            'rccm' => $this->rccm ? strtoupper(trim($this->rccm)) : null,
            'ifu' => $this->ifu ? preg_replace('/\D/', '', $this->ifu) : null,
            'message' => $this->message ? trim($this->message) : null,
        ]);
    }
}
