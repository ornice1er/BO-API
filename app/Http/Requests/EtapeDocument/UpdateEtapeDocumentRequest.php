<?php

namespace App\Http\Requests\EtapeDocument;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEtapeDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prestation_id'       => 'sometimes|integer|exists:prestations,id',
            'etape_id'            => 'sometimes|integer|exists:etapes,id',
            'name'                => 'sometimes|string|max:255',
            'slug'                => 'sometimes|string|max:255',
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
}
