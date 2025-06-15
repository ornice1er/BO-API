<?php

namespace App\Http\Requests\UserAuth;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ChangeFirstPasswordRequest extends FormRequest
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
            'password' => 'required|min:8|confirmed',
            'password_confirmation' => 'required|min:8',

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
            'password.required' => 'Le nouveau mot  de passe  est requis.',
            'password.min' => 'Le nouveau mot de passe doit être au minimun 8 caracteres.',
            'password.confirmed' => 'Les mots de passe doivent être identiques  .',
            'password_confirmation.required' => 'Le nouveau mot  de passe  est requis.',
            'password_confirmation.min' => 'Le nouveau mot de passe doit être au minimun 8 caracteres.',

        ];
    }
}
