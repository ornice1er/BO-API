<?php

namespace App\Http\Requests\EtapeDocument;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreEtapeDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prestation_id'       => 'required|integer|exists:prestations,id',
            'etape_id'            => 'required|integer|exists:etapes,id',
            'name'                => 'required|string|max:255',
            'slug'                => 'required|string|max:255',
            'is_required'         => 'nullable|boolean',
            'accepted_mime_types' => 'nullable|array',
            'accepted_mime_types.*'=> 'string',
            'max_size_kb'         => 'nullable|integer|min:1',
            'description'         => 'nullable|string|max:500',
            'order'               => 'nullable|integer|min:0',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages()
    {
        return [
            'prestation_id.required' => 'La prestation est requise.',
            'prestation_id.exists'   => 'La prestation sélectionnée n\'existe pas.',
            'etape_id.required'      => 'L\'étape est requise.',
            'etape_id.exists'        => 'L\'étape sélectionnée n\'existe pas.',
            'name.required'          => 'Le libellé est requis.',
            'slug.required'          => 'L\'identifiant technique est requis.',
        ];
    }
}
