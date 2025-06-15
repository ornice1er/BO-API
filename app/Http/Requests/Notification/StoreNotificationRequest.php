<?php

namespace App\Http\Requests\Notification;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreNotificationRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation appliquées à la requête.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => 'required|string',
            'notifiable_type' => 'required|string',
            'notifiable_id' => 'required|integer',
            'data' => 'required|string',
            'read_at' => 'nullable|date',
        ];
    }

    /**
     * Gestion des erreurs de validation.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    /**
     * Messages d'erreur personnalisés en français.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'title.required' => 'Le titre est requis.',
            'title.string' => 'Le titre doit être une chaîne de caractères.',
            'title.max' => 'Le titre ne doit pas dépasser 255 caractères.',
            'content.required' => 'Le contenu est requis.',
            'content.string' => 'Le contenu doit être une chaîne de caractères.',
            'is_read.boolean' => 'Le champ "lu" doit être vrai ou faux.',
        ];
    }

    protected function prepareForValidation() {}
}
