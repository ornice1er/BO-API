<?php

namespace App\Http\Requests\File;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UploadRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette requête.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Règles de validation pour la requête.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'input' => 'required|file|mimes:pdf,doc,docx,png,jpg,jpeg',
            'path' => 'required|string',
        ];
    }

    /**
     * Messages d'erreur en français.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'path.required' => 'Le path est requis.',
            'path.string' => 'Le path doit être une chaîne de caractères.',

            'input.file' => "L'entrée doit être un fichier.",
            'input.mimes' => "L'entrée doit être au format pdf, doc ou docx.",
            'input.max' => "L'entréene doit pas dépasser 2 Mo.",

        ];
    }

    /**
     * Traitement des erreurs de validation.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    /**
     * Préparation des données avant validation.
     */
    protected function prepareForValidation()
    {
        // Place pour préparer ou modifier les données avant validation, si nécessaire
    }
}
