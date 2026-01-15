<?php

namespace App\Http\Requests\Project;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProjectRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'filename' => 'nullable|string|max:255',

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
            'name.required' => 'Le nom du projet est requis.',
            'name.string' => 'Le nom du projet doit être une chaîne de caractères.',
            'name.max' => 'Le nom du projet ne doit pas dépasser 255 caractères.',
            'description.required' => 'La description du projet est requise.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.max' => 'La description ne doit pas dépasser 1000 caractères.',
            'pc_id.required' => 'La catégorie du projet est requise.',
            'pc_id.exists' => 'La catégorie du projet  n\'existe pas.',
            'pc_id.integer' => 'L\'id de Le département n\'est pas un entier .',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        // Custom preparation logic if needed
    }
}
