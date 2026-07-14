<?php

namespace App\Http\Requests\EtapePrestation;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreEtapePrestationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prestation_id'  => 'required|integer|exists:prestations,id',
            'etape_id'       => [
                'required', 'integer', 'exists:etapes,id',
                Rule::unique('etape_prestations')->where('prestation_id', $this->prestation_id),
            ],
            'sla_days'       => 'nullable|integer|min:0',
            'unite_admin_id' => 'nullable|integer|exists:unite_admins,id',
            'can_associate'  => 'nullable|boolean',
            'need_meeting'   => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'prestation_id.required' => 'La prestation est requise.',
            'etape_id.required'      => 'L\'étape est requise.',
            'etape_id.unique'        => 'Cette étape est déjà configurée pour cette prestation.',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }
}
