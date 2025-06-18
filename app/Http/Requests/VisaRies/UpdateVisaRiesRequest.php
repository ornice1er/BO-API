<?php

namespace App\Http\Requests\VisaRies;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVisaRiesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Ajustez selon vos besoins d'autorisation
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $visaRiesId = $this->route('visa_ries') ? $this->route('visa_ries')->id : $this->route('id');

        return [
            'name_structure' => 'sometimes|required|string|max:255',
            'rccm' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('visa_r_i_e_s', 'rccm')->ignore($visaRiesId)
            ],
            'ifu' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('visa_r_i_e_s', 'ifu')->ignore($visaRiesId)
            ],
            'message' => 'nullable|string',
            'requete_id' => 'sometimes|required|integer|exists:requetes,id', // Ajustez le nom de la table si nécessaire
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name_structure.required' => 'Le nom de la structure est obligatoire.',
            'name_structure.string' => 'Le nom de la structure doit être une chaîne de caractères.',
            'name_structure.max' => 'Le nom de la structure ne peut pas dépasser 255 caractères.',

            'rccm.string' => 'Le RCCM doit être une chaîne de caractères.',
            'rccm.max' => 'Le RCCM ne peut pas dépasser 255 caractères.',
            'rccm.unique' => 'Ce numéro RCCM existe déjà dans le système.',

            'ifu.string' => 'L\'IFU doit être une chaîne de caractères.',
            'ifu.max' => 'L\'IFU ne peut pas dépasser 255 caractères.',
            'ifu.unique' => 'Ce numéro IFU existe déjà dans le système.',

            'message.string' => 'Le message doit être une chaîne de caractères.',

            'requete_id.required' => 'L\'identifiant de la requête est obligatoire.',
            'requete_id.integer' => 'L\'identifiant de la requête doit être un nombre entier.',
            'requete_id.exists' => 'La requête spécifiée n\'existe pas.',
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
            'name_structure' => 'nom de la structure',
            'rccm' => 'RCCM',
            'ifu' => 'IFU',
            'message' => 'message',
            'requete_id' => 'identifiant de la requête',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Nettoyer les données avant validation si nécessaire
        $data = [];

        if ($this->has('name_structure')) {
            $data['name_structure'] = trim($this->name_structure);
        }

        if ($this->has('rccm')) {
            $data['rccm'] = $this->rccm ? trim($this->rccm) : null;
        }

        if ($this->has('ifu')) {
            $data['ifu'] = $this->ifu ? trim($this->ifu) : null;
        }

        if (!empty($data)) {
            $this->merge($data);
        }
    }
}
