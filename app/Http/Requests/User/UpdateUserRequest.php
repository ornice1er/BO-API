<?php

namespace App\Http\Requests\User;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
         $userId = $this->route('user');

        return [
             'roles' => 'array',
            'agent_id' => 'nullable|integer|exists:agents,id',
            'entite_admin_id' => 'nullable|integer|exists:entite_admins,id',
            'email' => 'required|email|max:255|unique:users,email,'. $userId ,
            'choices' => 'required|array'

        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Le code utilisateur est obligatoire.',
            'code.string' => 'Le code utilisateur doit être une chaîne de caractères.',
            'code.max' => 'Le code utilisateur ne peut pas dépasser 255 caractères.',
            'code.unique' => 'Ce code utilisateur existe déjà.',

            'agent_id.integer' => 'L\'identifiant de l\'agent doit être un nombre entier.',
            'agent_id.exists' => 'L\'agent sélectionné n\'existe pas.',

            'entite_admin_id.integer' => 'L\'identifiant de l\'entité administrative doit être un nombre entier.',
            'entite_admin_id.exists' => 'L\'entité administrative sélectionnée n\'existe pas.',

            'username.required' => 'Le nom d\'utilisateur est obligatoire.',
            'username.string' => 'Le nom d\'utilisateur doit être une chaîne de caractères.',
            'username.max' => 'Le nom d\'utilisateur ne peut pas dépasser 255 caractères.',
            'username.unique' => 'Ce nom d\'utilisateur existe déjà.',
            'username.alpha_dash' => 'Le nom d\'utilisateur ne peut contenir que des lettres, des chiffres, des tirets et des underscores.',

            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.max' => 'L\'adresse email ne peut pas dépasser 255 caractères.',
            'email.unique' => 'Cette adresse email existe déjà.',

            'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',

            'email_verified_at.date' => 'La date de vérification de l\'email doit être une date valide.',

            'first_signin.boolean' => 'Le champ première connexion doit être vrai ou faux.',
            'is_active.boolean' => 'Le champ actif doit être vrai ou faux.',
            'connected.boolean' => 'Le champ connecté doit être vrai ou faux.',
            'is_trade.boolean' => 'Le champ commerce doit être vrai ou faux.',

            'doc_pass.string' => 'Le document de passe doit être une chaîne de caractères.',
            'doc_pass.max' => 'Le document de passe ne peut pas dépasser 255 caractères.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'code' => 'code utilisateur',
            'agent_id' => 'agent',
            'entite_admin_id' => 'entité administrative',
            'username' => 'nom d\'utilisateur',
            'email' => 'adresse email',
            'password' => 'mot de passe',
            'email_verified_at' => 'date de vérification email',
            'first_signin' => 'première connexion',
            'is_active' => 'actif',
            'connected' => 'connecté',
            'doc_pass' => 'document de passe',
            'is_trade' => 'commerce',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Nettoyer les données booléennes si présentes
        if ($this->has('first_signin')) {
            $this->merge(['first_signin' => $this->boolean('first_signin')]);
        }
        

        if ($this->has('is_active')) {
            $this->merge(['is_active' => $this->boolean('is_active')]);
        }

        if ($this->has('connected')) {
            $this->merge(['connected' => $this->boolean('connected')]);
        }

        if ($this->has('is_trade')) {
            $this->merge(['is_trade' => $this->boolean('is_trade')]);
        }

        // Nettoyer les IDs
        if ($this->has('agent_id') && !empty($this->agent_id)) {
            $this->merge(['agent_id' => (int) $this->agent_id]);
        }

        if ($this->has('entite_admin_id') && !empty($this->entite_admin_id)) {
            $this->merge(['entite_admin_id' => (int) $this->entite_admin_id]);
        }


   
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
            // Validation personnalisée : un utilisateur ne peut pas se désactiver lui-même
            if ($this->has('is_active') && !$this->is_active) {
                $currentUser = auth()->user();
                $userId = $this->route('user') ? $this->route('user')->id : $this->route('user');

                if ($currentUser && $currentUser->id == $userId) {
                    $validator->errors()->add('is_active', 'Vous ne pouvez pas désactiver votre propre compte.');
                }
            }
        });


    }

      /**
     * Informations à afficher au cas où il y aurait des erreurs de validation
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

}


