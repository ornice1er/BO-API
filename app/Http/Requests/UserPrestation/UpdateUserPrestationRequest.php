<?php

namespace App\Http\Requests\UserPrestation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserPrestationRequest extends FormRequest
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
            'user_id' => 'required|exists:users,id',
            'prestation_id' => 'required|exists:prestations,id',
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Le champ utilisateur est obligatoire.',
            'user_id.exists' => "L'utilisateur sélectionné n'existe pas.",
            'prestation_id.required' => 'Le champ prestation est obligatoire.',
            'prestation_id.exists' => 'La prestation sélectionnée est invalide.',
        ];
    }
}
