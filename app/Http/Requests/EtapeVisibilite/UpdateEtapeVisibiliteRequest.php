<?php

namespace App\Http\Requests\EtapeVisibilite;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEtapeVisibiliteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workflow_transition_id' => 'sometimes|integer|exists:workflow_transitions,id',
            'role_name'              => 'sometimes|string|max:100',
            'can_read'               => 'nullable|boolean',
            'can_act'                => 'nullable|boolean',
            'scope_type'             => 'sometimes|in:requete,document',
            'doc_produit_id'         => 'nullable|integer|exists:etape_document_produits,id',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }
}
