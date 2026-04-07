<?php

namespace App\Http\Requests\EtapeVisibilite;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreEtapeVisibiliteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'workflow_transition_id' => 'required|integer|exists:workflow_transitions,id',
            'role_name'              => 'required|string|max:100',
            'can_read'               => 'nullable|boolean',
            'can_act'                => 'nullable|boolean',
            'scope_type'             => 'required|in:requete,document',
            'doc_produit_id'         => 'nullable|integer|exists:etape_document_produits,id|required_if:scope_type,document',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages()
    {
        return [
            'workflow_transition_id.required' => 'La transition est requise.',
            'workflow_transition_id.exists'   => 'La transition sélectionnée n\'existe pas.',
            'role_name.required'              => 'Le rôle est requis.',
            'scope_type.required'             => 'Le type de portée est requis.',
            'scope_type.in'                   => 'La portée doit être : requete ou document.',
            'doc_produit_id.required_if'      => 'Le document produit est requis pour une portée de type document.',
        ];
    }
}
