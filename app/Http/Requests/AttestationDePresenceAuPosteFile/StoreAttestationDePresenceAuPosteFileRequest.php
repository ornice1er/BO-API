<?php

namespace App\Http\Requests\AttestationDePresenceAuPosteFile;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttestationDePresenceAuPosteFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'type' => 'required|string|max:50',
            'filename' => 'required|string|max:255',
            'reference' => 'nullable|string|max:255',
            'adpap_id' => 'nullable|exists:attestation_de_presence_au_postes,id',
        ];
    }

    public function messages()
    {
        return [
            'type.required' => 'Le type de fichier est obligatoire.',
            'type.string' => 'Le type doit être une chaîne de caractères.',
            'filename.required' => 'Le nom du fichier est obligatoire.',
            'filename.max' => 'Le nom du fichier est trop long.',
            'reference.max' => 'La référence est trop longue.',
            'adpap_id.exists' => 'L’identifiant de l’attestation est invalide.',
        ];
    }
}
