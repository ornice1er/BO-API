<?php

namespace App\Http\Requests\Affectation;

use Illuminate\Foundation\Http\FormRequest;

class StoreAffectationRequest extends FormRequest
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
            //'isLast' => 'required|boolean',
            'unite_admin_id' => 'nullable|integer|exists:unite_admins,id',
            'unite_admin_down_id' => 'nullable|integer|exists:unite_admins,id',
            'sens' => 'required|numeric',
            'requete_id' => 'nullable|exists:requetes,id',
            'instruction' => 'nullable|string',
            'delay' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'isLast.required' => 'Le champ "Dernière affectation ?" est obligatoire.',
            'unite_admin_up.required' => 'L\'unité administrative source est requise.',
            'unite_admin_down.required' => 'L\'unité administrative de destination est requise.',
            'unite_admin_up.exists' => 'L\'unité source sélectionnée est invalide.',
            'unite_admin_down.exists' => 'L\'unité de destination sélectionnée est invalide.',
            'sens.required' => 'Le sens de l\'affectation est obligatoire.',
            'sens.in' => 'Le sens doit être 1 (vers le bas) ou 2 (vers le haut).',
            'requete_id.exists' => 'La requête associée est invalide.',
            'delay.integer' => 'Le délai doit être un nombre entier.',
            'delay.min' => 'Le délai ne peut pas être négatif.',
        ];
    }
}
