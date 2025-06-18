<?php

namespace App\Http\Requests\AgrementMedecin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAgrementMedecinRequest extends FormRequest
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
            'code' => 'sometimes|required|uuid',
            'ifu' => 'nullable|string|max:255',
            'identity' => 'sometimes|required|string|max:255',
            'requete_id' => 'nullable|exists:requetes,id',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Le code est obligatoire.',
            'code.uuid' => 'Le code doit être un UUID valide.',
            'identity.required' => 'L\'identité est obligatoire.',
            'requete_id.exists' => 'La requête spécifiée est invalide.',
        ];
    }
}
