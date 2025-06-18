<?php

namespace App\Http\Requests\UniteAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUniteAdminRequest extends FormRequest
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
        return [
            'code' => [
                'required',
                'uuid',
                'unique:unite_admins,code'
            ],
            'libelle' => [
                'required',
                'string',
                'max:255'
            ],
            'type_unite_admin_id' => [
                'required',
                'integer',
                'exists:type_unite_admins,id'
            ],
            'sigle' => [
                'nullable',
                'string',
                'max:50',
                'unique:unite_admins,sigle'
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                'unique:unite_admins,email'
            ],
            'entite_admin_id' => [
                'nullable',
                'integer',
                'exists:entite_admins,id'
            ],
            'ua_parent_code' => [
                'nullable',
                'uuid',
                'exists:unite_admins,code'
            ],
            'department_id' => [
                'nullable',
                'integer',
                'exists:departments,id'
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
            // Messages pour le code
            'code.required' => 'Le code est obligatoire.',
            'code.uuid' => 'Le code doit être un UUID valide.',
            'code.unique' => 'Ce code existe déjà dans le système.',

            // Messages pour le libellé
            'libelle.required' => 'Le libellé est obligatoire.',
            'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
            'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',

            // Messages pour le type d'unité administrative
            'type_unite_admin_id.required' => 'Le type d\'unité administrative est obligatoire.',
            'type_unite_admin_id.integer' => 'Le type d\'unité administrative doit être un nombre entier.',
            'type_unite_admin_id.exists' => 'Le type d\'unité administrative sélectionné n\'existe pas.',

            // Messages pour le sigle
            'sigle.string' => 'Le sigle doit être une chaîne de caractères.',
            'sigle.max' => 'Le sigle ne peut pas dépasser 50 caractères.',
            'sigle.unique' => 'Ce sigle existe déjà dans le système.',

            // Messages pour l'email
            'email.email' => 'L\'adresse email doit être valide.',
            'email.max' => 'L\'adresse email ne peut pas dépasser 255 caractères.',
            'email.unique' => 'Cette adresse email existe déjà dans le système.',

            // Messages pour l'entité administrative
            'entite_admin_id.integer' => 'L\'entité administrative doit être un nombre entier.',
            'entite_admin_id.exists' => 'L\'entité administrative sélectionnée n\'existe pas.',

            // Messages pour l'unité administrative parente
            'ua_parent_code.uuid' => 'Le code de l\'unité administrative parente doit être un UUID valide.',
            'ua_parent_code.exists' => 'L\'unité administrative parente sélectionnée n\'existe pas.',

            // Messages pour le département
            'department_id.integer' => 'Le département doit être un nombre entier.',
            'department_id.exists' => 'Le département sélectionné n\'existe pas.'
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
            'libelle' => 'libellé',
            'type_unite_admin_id' => 'type d\'unité administrative',
            'sigle' => 'sigle',
            'email' => 'adresse email',
            'entite_admin_id' => 'entité administrative',
            'ua_parent_code' => 'unité administrative parente',
            'department_id' => 'département'
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Vérifier que l'unité parente n'est pas elle-même
            if ($this->ua_parent_code && $this->ua_parent_code === $this->code) {
                $validator->errors()->add(
                    'ua_parent_code',
                    'Une unité administrative ne peut pas être sa propre parente.'
                );
            }
        });
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
                'message' => 'Erreur de validation des données.',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}