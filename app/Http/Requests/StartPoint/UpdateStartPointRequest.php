<?php

namespace App\Http\Requests\StartPoint;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStartPointRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Ajustez selon vos besoins d'autorisation
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'prestation_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:prestations,id',
                // Règle d'unicité pour éviter les doublons (prestation_id + unite_admin_id)
                Rule::unique('start_points')->where(function ($query) {
                    return $query->where('unite_admin_id', $this->unite_admin_id);
                })->ignore($this->route('start_point')),
            ],
            'unite_admin_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:unite_admins,id',
                // Règle d'unicité pour éviter les doublons (prestation_id + unite_admin_id)
                Rule::unique('start_points')->where(function ($query) {
                    return $query->where('prestation_id', $this->prestation_id);
                })->ignore($this->route('start_point')),
            ],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'prestation_id.required' => 'La prestation est obligatoire.',
            'prestation_id.integer' => 'L\'identifiant de la prestation doit être un nombre entier.',
            'prestation_id.exists' => 'La prestation sélectionnée n\'existe pas.',
            'prestation_id.unique' => 'Cette combinaison prestation/unité administrative existe déjà.',

            'unite_admin_id.required' => 'L\'unité administrative est obligatoire.',
            'unite_admin_id.integer' => 'L\'identifiant de l\'unité administrative doit être un nombre entier.',
            'unite_admin_id.exists' => 'L\'unité administrative sélectionnée n\'existe pas.',
            'unite_admin_id.unique' => 'Cette combinaison prestation/unité administrative existe déjà.',
        ];
    }

    /**
     * Get custom attribute names for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'prestation_id' => 'prestation',
            'unite_admin_id' => 'unité administrative',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Nettoyer les données si nécessaire
        if ($this->has('prestation_id')) {
            $this->merge(['prestation_id' => (int) $this->prestation_id]);
        }

        if ($this->has('unite_admin_id')) {
            $this->merge(['unite_admin_id' => (int) $this->unite_admin_id]);
        }
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validation personnalisée supplémentaire si nécessaire
            if ($this->has('prestation_id') && $this->has('unite_admin_id')) {
                // Vérifier si la combinaison existe déjà (en excluant l'enregistrement actuel)
                $exists = \App\Models\StartPoint::where('prestation_id', $this->prestation_id)
                    ->where('unite_admin_id', $this->unite_admin_id)
                    ->where('id', '!=', $this->route('start_point'))
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('combination', 'Cette combinaison prestation/unité administrative existe déjà.');
                }
            }
        });
    }
}
