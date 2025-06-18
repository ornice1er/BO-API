<?php

namespace App\Http\Requests\AttestationDePresenceAuPosteFile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttestationDePresenceAuPosteFileRequest extends FormRequest
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
        return (new StoreAttestationDePresenceAuPosteFileRequest)->messages(); // Réutilisation des messages
    }
}
