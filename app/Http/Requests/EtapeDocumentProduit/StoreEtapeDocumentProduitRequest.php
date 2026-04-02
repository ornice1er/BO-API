<?php

namespace App\Http\Requests\EtapeDocumentProduit;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreEtapeDocumentProduitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prestation_id'       => 'required|integer|exists:prestations,id',
            'name'                => 'required|string|max:255',
            'slug'                => 'required|string|max:255',
            'type'                => 'required|in:lettre,decision,attestation,pv',
            'numero_prefix'       => 'nullable|string|max:10',
            'template_key'        => 'required|string|max:255',
            'etape_edition_id'    => 'required|integer|exists:etapes,id',
            'etape_delivrance_id' => 'nullable|integer|exists:etapes,id',
            'allow_correction'    => 'nullable|boolean',
            'order'               => 'nullable|integer|min:1',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages()
    {
        return [
            'prestation_id.required'    => 'La prestation est requise.',
            'prestation_id.exists'      => 'La prestation sélectionnée n\'existe pas.',
            'name.required'             => 'Le libellé est requis.',
            'slug.required'             => 'L\'identifiant technique est requis.',
            'type.required'             => 'Le type de document est requis.',
            'type.in'                   => 'Le type doit être : lettre, decision, attestation ou pv.',
            'template_key.required'     => 'La clé de template est requise.',
            'etape_edition_id.required' => 'L\'étape d\'édition est requise.',
            'etape_edition_id.exists'   => 'L\'étape d\'édition sélectionnée n\'existe pas.',
            'etape_delivrance_id.exists'=> 'L\'étape de délivrance sélectionnée n\'existe pas.',
        ];
    }
}
