<?php

namespace App\Http\Requests\Role;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateRoleRequest extends FormRequest
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
            'name' => 'sometimes|string|max:255|unique:roles,name,'.$this->route('role'), // Exclut le rôle actuel lors de la mise à jour
            'guard_name' => 'sometimes|string|max:255',
            'permissions' => 'nullable|array',
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
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'name.unique' => 'Ce nom de rôle existe déjà.',
            'guard_name.string' => 'Le champ "guard_name" doit être une chaîne de caractères.',
            'guard_name.max' => 'Le champ "guard_name" ne doit pas dépasser 255 caractères.',
        ];
    }

    protected function prepareForValidation()
    {
        // Vous pouvez préparer les données avant la validation ici, si nécessaire
    }
}
