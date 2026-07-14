<?php

namespace App\Http\Requests\DocumentCircuitEtape;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateDocumentCircuitEtapeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doc_produit_id'       => 'sometimes|integer|exists:etape_document_produits,id',
            'unite_admin_id'       => 'nullable|integer|exists:unite_admins,id',
            'role_name'            => 'sometimes|string|max:100',
            'action_type'          => 'sometimes|in:edition,paraphe,prevalidation,signature,correction',
            'can_act_pns'          => 'nullable|boolean',
            'decision'             => 'nullable|string|max:255',
            'status_after'         => 'sometimes|string|max:100',
            'requete_status_after' => 'nullable|string|max:100',
            'is_blocking'          => 'nullable|boolean',
            'order'                => 'sometimes|integer|min:1',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }
}
