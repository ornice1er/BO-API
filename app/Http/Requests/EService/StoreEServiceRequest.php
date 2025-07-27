<?php

namespace App\Http\Requests\EService;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreEserviceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.\
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
         return [
            'steps' => ['required', 'array'],
            'files' => ['sometimes', 'string'],
            'meta' => ['required', 'array'],
            'meta.code' => ['required', 'string'],
            'meta.prestation_code' => ['required', 'string'],
            'meta.info' => ['required']
        ];
    }

    /**
     * Informations à afficher au cas où il y aurait des erreurs de validation
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    /**
     * Mettre les messages d'erreur en Français
     *
     * @return array
     */
    public function messages()
    {
         return [
            'steps.required' => 'Le tableau des étapes (steps) est requis.',
            'steps.array' => 'Le champ steps doit être un tableau.',
            'meta.required' => 'Le champ meta est requis.',
            'meta.array' => 'Le champ meta doit être un objet.',
            'meta.code.required' => 'Le champ code est requis dans meta.',
            'meta.code.string' => 'Le champ code doit être une chaîne de caractères.',
            'meta.prestation_code.required' => 'Le champ prestation_code est requis dans meta.',
            'meta.prestation_code.string' => 'Le champ prestation_code doit être une chaîne de caractères.',
            
            'meta.info.required' => 'Le champ info est requis dans meta.',
            'meta.info.array' => 'Le champ info doit être un objet (tableau associatif).',
        ];
    }

    protected function prepareForValidation() {}
}
