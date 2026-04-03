<?php

namespace App\Http\Requests\MotifRejet;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreMotifRejetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prestation_id'   => 'nullable|integer|exists:prestations,id',
            'etape_id'        => 'nullable|integer|exists:etapes,id',
            'code'            => 'required|string|max:50',
            'libelle'         => 'required|string|max:255',
            'description'     => 'nullable|string',
            'allow_complement'=> 'nullable|boolean',
            'is_final'        => 'nullable|boolean',
            'is_active'       => 'nullable|boolean',
            'order'           => 'nullable|integer|min:0',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages()
    {
        return [
            'code.required'    => 'Le code est requis.',
            'libelle.required' => 'Le libellé est requis.',
        ];
    }
}
