<?php

namespace App\Http\Requests\UserAuth;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
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
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8',
            'device' => 'required|in:web,mobile',
            'code_otp' => 'nullable|integer',
            'new_connexion_canal' => 'nullable|in:SMS,WHATSAPP,EMAIL',
            'canal_value' => 'required_with:new_connexion_canal',

        ];
    }

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
            'email.exists' => "L'email n'existe pas.",
            'password.required' => 'Le mot de  passe est requis.',
            'password.min' => 'Le mot de passe doit comporter au moins 8 caractères.',
            'code_otp.integer' => 'Le code otp doit être un entier.',
        ];
    }
}
