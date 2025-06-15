<?php

namespace App\Http\Requests\OTP;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerifyOTPRequest extends FormRequest
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
            'phone' => 'nullable|string', // prevoir une regex
            'email' => 'nullable|email|string',
            'author' => 'required_if:for_login,true|email|string',
            'for_login' => 'nullable|boolean',
            'verification_code' => 'required|integer',
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
            'phone.integer' => 'Le numéro doit être un entier précédé commençant par +229',
            'phone.required' => 'Le numéro est requis.',
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être une adresse email valide.',
            'email.string' => 'L\'email doit être une chaîne de caractère.',
            'verification_code.required' => 'Le code est requis',
            'verification_code.integer' => 'Le code  doit être un entier',

        ];
    }
}
