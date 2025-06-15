<?php

namespace App\Http\Requests\Village;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateVillageRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'district_id' => 'required|integer|exists:districts,id',
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
            'name.string' => 'Le nom doit être une chaîne de caractères.',
            'name.required' => 'Le nom est requis.',
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'district_id.required' => 'L\'arrondissement est requis.',
            'district_id.exists' => 'L\'arrondissement choisi  n\'existe pas.',
            'district_id.integer' => 'L\'id de l\'arrondissement n\'est pas un entier .',
        ];
    }

    protected function prepareForValidation()
    {
        // $this->mergeIfMissing(['is_active' => false]);
    }
}
