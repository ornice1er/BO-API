<?php

namespace App\Http\Requests\Parcours;

use Illuminate\Foundation\Http\FormRequest;

class StoreParcoursRequest extends FormRequest
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
            'libelle' => 'required|string|max:255',
            'requete_id' => 'required|exists:requetes,id',
            'user_id' => 'nullable|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'libelle.required' => 'Le libellé est obligatoire.',
            'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
            'libelle.max' => 'Le libellé ne doit pas dépasser 255 caractères.',
            'requete_id.required' => 'L\'identifiant de la requête est obligatoire.',
            'requete_id.exists' => 'La requête sélectionnée est invalide.',
            'user_id.exists' => 'L\'utilisateur sélectionné est invalide.',
        ];
    }
}
