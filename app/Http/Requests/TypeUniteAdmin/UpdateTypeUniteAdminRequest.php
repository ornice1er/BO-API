<?php

namespace App\Http\Requests\TypeUniteAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateTypeUniteAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Ajustez selon vos besoins d'autorisation
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $typeUniteAdminId = $this->route('type_unite_admin') ?? $this->route('id');

        return [
            // 'code' => [
            //     'sometimes',
            //     'required',
            //     'uuid',
            //     Rule::unique('type_unite_admins', 'code')->ignore($typeUniteAdminId)
            // ],
            'libelle' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('type_unite_admins', 'libelle')->ignore($typeUniteAdminId)
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'code.required' => 'Le code est obligatoire.',
            'code.uuid' => 'Le code doit être un UUID valide.',
            'code.unique' => 'Ce code existe déjà dans le système.',

            'libelle.required' => 'Le libellé est obligatoire.',
            'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
            'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',
            'libelle.unique' => 'Ce libellé existe déjà dans le système.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'code' => 'code',
            'libelle' => 'libellé'
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Supprimer les champs vides ou null pour permettre une mise à jour partielle
        $this->merge(
            array_filter($this->all(), function ($value) {
                return $value !== null && $value !== '';
            })
        );
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Erreur de validation des données pour la mise à jour.',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}