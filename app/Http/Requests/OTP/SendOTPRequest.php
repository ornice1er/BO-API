<?php

namespace App\Http\Requests\OTP;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SendOTPRequest extends FormRequest
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
            'phone' => 'required|string', // prevoir une regex
            'email' => 'required|email|string',
            'canal' => 'required|array|in:SMS,EMAIL,APP',
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
            'phone.string' => 'Le numéro doit être un entier précédé commençant par +229',
            'phone.required' => 'Le numéro est requis.',
            'email.required' => 'L\'email est requis.',
            'email.email' => 'L\'email doit être une adresse email valide.',
            'email.string' => 'L\'email doit être une chaîne de caractère.',
            'canal.required' => 'Le canal est requis',
            'canal.array' => 'Le canal doit être un tableau',
            'canal.in' => 'Le canal doit contenir suivant: SMS, WHATSAPP et/ou EMAIL',

        ];
    }
}
