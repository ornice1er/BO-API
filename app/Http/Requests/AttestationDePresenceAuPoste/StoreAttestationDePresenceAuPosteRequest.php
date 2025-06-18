<?php

namespace App\Http\Requests\AttestationDePresenceAuPoste;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttestationDePresenceAuPosteRequest extends FormRequest
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
            'code' => 'required|uuid',
            'identity' => 'required|string|max:255',
            'matricule' => 'nullable|string|max:255',
            'structure' => 'nullable|string|max:255',
            'corporate' => 'nullable|string|max:255',
            'date_job' => 'nullable|date',
            'sex' => 'nullable|string|max:20',
            'requete_id' => 'nullable|exists:requetes,id',
            'status' => 'nullable|string|max:50',
            'unite_admin_id' => 'nullable|exists:unites_admins,id',
            'birthday' => 'nullable|date',
            'birthplace' => 'nullable|string|max:255',
            'function' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'scale' => 'nullable|string|max:255',
            'level' => 'nullable|string|max:255',
            'bride' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Le code est obligatoire.',
            'code.uuid' => 'Le code doit être un UUID valide.',
            'identity.required' => 'Le nom complet est obligatoire.',
            'requete_id.exists' => 'La requête associée est invalide.',
            'unite_admin_id.exists' => 'L’unité administrative sélectionnée est invalide.',
            'birthday.date' => 'La date de naissance doit être une date valide.',
            'date_job.date' => 'La date de prise de service doit être une date valide.',
        ];
    }
}
