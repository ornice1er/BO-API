<?php

namespace App\Http\Requests\EtapeDocumentProduit;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEtapeDocumentProduitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prestation_id'       => 'sometimes|integer|exists:prestations,id',
            'name'                => 'sometimes|string|max:255',
            'slug'                => 'sometimes|string|max:255',
            'type'                => 'sometimes|in:lettre,decision,attestation,pv',
            'numero_prefix'       => 'nullable|string|max:10',
            'template_key'        => 'sometimes|string|max:255',
            'etape_edition_id'    => 'sometimes|integer|exists:etapes,id',
            'etape_delivrance_id' => 'nullable|integer|exists:etapes,id',
            'allow_correction'    => 'nullable|boolean',
            'order'               => 'nullable|integer|min:1',
            "generate_from"       => 'sometimes|in:pns,system',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }
}
