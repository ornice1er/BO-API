<?php

namespace App\Http\Requests\User;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StorePRUserRequest extends FormRequest
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
            'email' => 'required|email|unique:users,email',
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'municipality_id' => 'required|integer|exists:municipalities,id',
            'project_id' => 'required',
            'personne_ressource_id' => 'required|integer',
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

            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être une adresse email valide.',
            'email.unique' => 'L\'email est déjà utilisé.',
            'firstname.required' => 'Le prénom est requis.',
            'firstname.string' => 'Le prénom doit être une chaîne de caractères.',
            'firstname.max' => 'Le prénom ne doit pas dépasser 100 caractères.',
            'lastname.required' => 'Le nom de famille est requis.',
            'lastname.string' => 'Le nom de famille doit être une chaîne de caractères.',
            'lastname.max' => 'Le nom de famille ne doit pas dépasser 100 caractères.',
            'birthdate.required' => 'La date de naissance est requise.',
            'birthdate.date' => 'La date de naissance doit être une date valide.',
            'birthplace.required' => 'Le lieu de naissance est requis.',
            'birthplace.string' => 'Le lieu de naissance doit être une chaîne de caractères.',
            'birthplace.max' => 'Le lieu de naissance ne doit pas dépasser 255 caractères.',
            'address.required' => 'L\'adresse est requise.',
            'address.string' => 'L\'adresse doit être une chaîne de caractères.',
            'address.max' => 'L\'adresse ne doit pas dépasser 255 caractères.',
            'phone.required' => 'Le numéro de téléphone est requis.',
            'phone.string' => 'Le numéro de téléphone doit être une chaîne de caractères.',
            'phone.max' => 'Le numéro de téléphone ne doit pas dépasser 20 caractères.',
            'projects.array' => 'Il est attendu un tableau d\'id de projet.',
            'municipality_id.required' => 'La commune est requise.',
            'municipality_id.exists' => 'La commune  n\'existe pas.',
            'municipality_id.integer' => 'L\'id de la commune n\'est pas un entier .',
            'spoken_languages.required' => 'Le tableau d\'id de langues parlées est requis.',
            'spoken_languages.array' => 'un tableau d\'id de de langues parlées est attendu.',
            'understood_languages.required' => 'Le tableau d\'id de langues compries est requis.',
            'understood_languages.array' => 'un tableau d\'id de de langues compries est attendu.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        //

    }
}
