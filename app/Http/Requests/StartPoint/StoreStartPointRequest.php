<?php

namespace App\Http\Requests\StartPoint;

use Illuminate\Foundation\Http\FormRequest;

class StoreStartPointRequest extends FormRequest
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
            'prestation_id' => 'required|integer|exists:prestations,id',
            'unite_admin_id' => 'required|integer|exists:unite_admins,id',
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

            'unite_admin_id.required' => 'L\'unité administrative est obligatoire.',
            'unite_admin_id.integer' => 'L\'identifiant de l\'unité administrative doit être un nombre entier.',
            'unite_admin_id.exists' => 'L\'unité administrative sélectionnée n\'existe pas.',
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
        $this->merge([
            'prestation_id' => (int) $this->prestation_id,
            'unite_admin_id' => (int) $this->unite_admin_id,
        ]);
    }
}
