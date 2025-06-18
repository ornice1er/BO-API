<?php

namespace App\Http\Requests\Requete;

use Illuminate\Foundation\Http\FormRequest;

class StoreRequeteRequest extends FormRequest
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
            'code' => 'required|string|unique:requetes,code',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'filename' => 'nullable|string',
            'prestation_id' => 'nullable|exists:prestations,id',
            'header' => 'nullable|json',
            'attach' => 'nullable|string',
            'comment' => 'nullable|string',
            'content' => 'nullable|string',
            'content2' => 'nullable|string',
            'content3' => 'nullable|string',
            'finaleResponse' => 'nullable|string',
            'step_contents' => 'nullable|json',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Le code est obligatoire.',
            'code.unique' => 'Ce code existe déjà.',
            'email.email' => 'L’adresse email n’est pas valide.',
            'prestation_id.exists' => 'La prestation sélectionnée est invalide.',
            'header.json' => 'Le champ header doit être un JSON valide.',
            'step_contents.json' => 'Le champ step_contents doit être un JSON valide.',
        ];
    }
}
