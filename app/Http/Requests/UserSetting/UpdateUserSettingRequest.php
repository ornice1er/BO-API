<?php

namespace App\Http\Requests\UserSetting;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateUserSettingRequest extends FormRequest
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
            'use_2FA' => 'nullable|boolean',
            'accept_notification' => 'nullable|boolean',
            'notification_list' => 'nullable|array',
            'notification_list.*' => 'string|max:255',
            'mode_2FA' => 'nullable|string|in:SMS,EMAIL,WHATSAPP',
        ];
    }

    /**
     * Informations à afficher en cas d'erreur de validation
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    /**
     * Messages d'erreur personnalisés en Français
     *
     * @return array
     */
    public function messages()
    {
        return [
            'user_id.required' => 'L\'identifiant de l\'utilisateur est requis.',
            'user_id.integer' => 'L\'identifiant de l\'utilisateur doit être un nombre entier.',
            'user_id.exists' => 'L\'utilisateur spécifié n\'existe pas.',
            'use_2FA.boolean' => 'Le paramètre d\'authentification à deux facteurs doit être vrai ou faux.',
            'accept_notification.boolean' => 'Le paramètre de notification doit être vrai ou faux.',
            'notification_list.array' => 'La liste de notifications doit être un tableau.',
            'notification_list.*.string' => 'Chaque élément de la liste de notifications doit être une chaîne de caractères.',
            'notification_list.*.max' => 'Chaque élément de la liste de notifications ne doit pas dépasser 255 caractères.',
            'mode_2FA.string' => 'Le mode d\'authentification à deux facteurs doit être une chaîne de caractères.',
            'mode_2FA.in' => 'Le mode d\'authentification doit être l\'un des suivants : sms, email ou app.',
        ];
    }

    /**
     * Préparation des données avant la validation
     */
    protected function prepareForValidation()
    {
        // Vous pouvez préparer ou ajuster les données avant la validation si nécessaire.
        // Par exemple, convertir les valeurs en booléen :
        // $this->merge([
        //     'use_2FA' => filter_var($this->use_2FA, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
        //     'accept_notification' => filter_var($this->accept_notification, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
        // ]);
    }
}
