<?php

namespace App\Http\Requests\Municipality;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateMunicipalityGhmRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'project_id' => 'required|integer|exists:projects,id',
            'edition_id' => 'required|integer',
            'department_id' => 'required|integer|exists:departments,id',

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
            'name.string' => 'Le nom doit être une chaîne de caractères.',
            'name.required' => 'Le nom est requis.',
            'name.max' => 'Le nom ne doit pas dépasser 255 caratères.',
            'project_id.required' => 'Le department est requis.',
            'project_id.exists' => 'Le department choisi  n\'existe pas.',
            'project_id.integer' => 'L\'id de department n\'est pas un entier .',
        ];
    }

    protected function prepareForValidation()
    {
        // $this->mergeIfMissing(['is_active' => false]);

    }
}
