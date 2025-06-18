<?php

namespace App\Http\Requests\AdherantMemberCmps;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdherantMemberCmpsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_date' => 'nullable|date',
            'identity' => 'nullable|string|max:255',
            'birthday' => 'nullable|date',
            'birthplace' => 'nullable|string|max:255',
            'sex' => 'nullable|string|max:10',
            'identity_mother' => 'nullable|string|max:255',
            'profession' => 'nullable|string|max:255',
            'filiation' => 'nullable|string|max:255',
            'adh_id' => 'nullable|exists:adherant_cmps,id',
        ];
    }

    public function messages(): array
    {
        return [
            'start_date.date' => 'La date de début doit être une date valide.',
            'identity.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'birthday.date' => 'La date de naissance doit être une date valide.',
            'birthplace.max' => 'Le lieu de naissance ne doit pas dépasser 255 caractères.',
            'sex.max' => 'Le sexe ne doit pas dépasser 10 caractères.',
            'identity_mother.max' => 'L\'identité de la mère ne doit pas dépasser 255 caractères.',
            'profession.max' => 'La profession ne doit pas dépasser 255 caractères.',
            'filiation.max' => 'La filiation ne doit pas dépasser 255 caractères.',
            'adh_id.exists' => 'L\'adhérent parent est invalide.',
        ];
    }
}
