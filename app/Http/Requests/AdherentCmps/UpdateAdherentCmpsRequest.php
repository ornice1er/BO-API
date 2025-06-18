<?php

namespace App\Http\Requests\AdherantCmps;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdherantCmpsRequest extends FormRequest
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
            'code' => 'nullable|string|max:255',
            'identity' => 'nullable|string|max:255',
            'birthday' => 'nullable|date',
            'birthplace' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'assoc_name' => 'nullable|string|max:255',
            'economic_activity' => 'nullable|string|max:255',
            'profession' => 'nullable|string|max:255',
            'adherance_fee' => 'nullable|string|max:255',
            'adherance_fee_date' => 'nullable|date',
            'first_contributtion' => 'nullable|string|max:255',
            'first_contributtion_date' => 'nullable|date',
            'requete_id' => 'nullable|exists:requetes,id',
        ];
    }

    public function messages(): array
    {
        return [
            'code.max' => 'Le code ne doit pas dépasser 255 caractères.',
            'identity.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'birthday.date' => 'La date de naissance doit être une date valide.',
            'birthplace.max' => 'Le lieu de naissance ne doit pas dépasser 255 caractères.',
            'address.max' => 'L\'adresse ne doit pas dépasser 255 caractères.',
            'assoc_name.max' => 'Le nom de l\'association ne doit pas dépasser 255 caractères.',
            'economic_activity.max' => 'L\'activité économique ne doit pas dépasser 255 caractères.',
            'profession.max' => 'La profession ne doit pas dépasser 255 caractères.',
            'adherance_fee.max' => 'Le montant d\'adhésion ne doit pas dépasser 255 caractères.',
            'adherance_fee_date.date' => 'La date d\'adhésion doit être une date valide.',
            'first_contributtion.max' => 'La première cotisation ne doit pas dépasser 255 caractères.',
            'first_contributtion_date.date' => 'La date de première cotisation doit être une date valide.',
            'requete_id.exists' => 'La requête associée est invalide.',
        ];
    }
}
