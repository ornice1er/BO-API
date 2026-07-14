<?php

namespace App\Http\Requests\EtapePrestation;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateEtapePrestationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sla_days'       => 'nullable|integer|min:0',
            'unite_admin_id' => 'nullable|integer|exists:unite_admins,id',
            'can_associate'  => 'nullable|boolean',
            'need_meeting'   => 'nullable|boolean',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }
}
