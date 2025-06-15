<?php

namespace App\Http\Requests\Setting;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateSettingRequest extends FormRequest
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
            'key' => 'required|string|max:255',
            'value' => 'nullable|string|max:1000',
            'durer' => 'nullable|integer|min:1',
            'seance_interactive' => 'nullable|integer|min:1',
            'dure_deuiduite' => 'nullable|integer|min:0',
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
            'key.required' => 'La clé est requise.',
            'key.string' => 'La clé doit être une chaîne de caractères.',
            'key.max' => 'La clé ne doit pas dépasser 255 caractères.',
            'value.string' => 'La valeur doit être une chaîne de caractères.',
            'value.max' => 'La valeur ne doit pas dépasser 1000 caractères.',
            'durer.integer' => 'La durée doit être un nombre entier.',
            'durer.min' => 'La durée doit être au moins 1.',
            'seance_interactive.integer' => 'La séance interactive doit être un nombre entier.',
            'seance_interactive.min' => 'La séance interactive doit être au moins 1.',
            'dure_deuiduite.integer' => 'La durée déduite doit être un nombre entier.',
            'dure_deuiduite.min' => 'La durée déduite ne peut pas être négative.',
        ];
    }

    /**
     * Préparation des données avant la validation
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'durer' => $this->durer ?? 120,
            'seance_interactive' => $this->seance_interactive ?? 120,
            'dure_deuiduite' => $this->dure_deuiduite ?? 1,
        ]);
    }
}
