<?php

namespace App\Http\Requests\Department;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreDepartmentRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'country_id' => 'required|integer|exists:countries,id',

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
            'department_id.required' => 'Le département est requis.',
            'department_id.exists' => 'Le département choisi  n\'existe pas.',
            'department_id.integer' => 'L\'id de Le département n\'est pas un entier .',
        ];
    }

    protected function prepareForValidation() {}
}
