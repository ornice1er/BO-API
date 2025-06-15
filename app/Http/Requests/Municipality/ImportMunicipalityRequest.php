<?php

namespace App\Http\Requests\Municipality;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ImportMunicipalityRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation pour la requête.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'excel_file' => 'required|mimes:xls,xlsx',
        ];
    }

    /**
     * Informations à afficher en cas d'erreurs de validation.
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    /**
     * Messages d'erreur en français.
     *
     * @return array
     */
    public function messages()
    {
        return [

        ];
    }

    protected function prepareForValidation()
    {
        // Place pour préparer ou modifier les données avant validation, si nécessaire
    }
}
