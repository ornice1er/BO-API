<?php

namespace App\Http\Requests\DocumentCircuitEtape;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDocumentCircuitEtapeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doc_produit_id'       => 'required|integer|exists:etape_document_produits,id',
            'unite_admin_id'       => 'nullable|integer|exists:unite_admins,id',
            'role_name'            => 'required|string|max:100',
            'action_type'          => 'required|in:edition,paraphe,prevalidation,signature,correction',
            'status_after'         => 'required|string|max:100',
            'requete_status_after' => 'nullable|string|max:100',
            'is_blocking'          => 'nullable|boolean',
            'order'                => 'required|integer|min:1',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages()
    {
        return [
            'doc_produit_id.required' => 'Le document produit est requis.',
            'doc_produit_id.exists'   => 'Le document produit sélectionné n\'existe pas.',
            'role_name.required'      => 'Le rôle requis est obligatoire.',
            'action_type.required'    => 'Le type d\'action est requis.',
            'action_type.in'          => 'Le type d\'action doit être : edition, paraphe, prevalidation, signature ou correction.',
            'status_after.required'   => 'Le statut document après est requis.',
            'order.required'          => 'L\'ordre est requis.',
        ];
    }
}
