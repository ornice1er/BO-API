<?php

namespace App\Http\Requests\UserProject;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserProjectRequest extends FormRequest
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
            'user_id' => 'required|integer|exists:users,id',
            'project_id' => 'required|integer|exists:projects,id',
            'roles' => 'required|array',
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
            'user_id.required' => 'L\' utilisateur est requise.',
            'user_id.exists' => 'L\'utilisateur choisi  n\'existe pas.',
            'user_id.integer' => 'L\'id de la utilisateur n\'est pas un entier .',
            'project_id.required' => 'Le projet est requise.',
            'project_id.exists' => 'Le projet choisi  n\'existe pas.',
            'project_id.integer' => 'L\'id du projet n\'est pas un entier .',
            'roles.array' => 'Il est attendu un tableau d\'id de role.',
            'roles.required' => 'Les roles sont requis.',

        ];
    }

    protected function prepareForValidation()
    {
        // $this->mergeIfMissing(['is_active' => false]);

    }
}
