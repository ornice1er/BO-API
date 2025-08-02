<?php

namespace App\Http\Requests\Commission;

use Illuminate\Foundation\Http\FormRequest;

class AddRequetesToCommissionRequest extends FormRequest
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
            'requete_ids' => 'required|array',
            'requete_ids.*' => 'integer|exists:requetes,id',
            'status' => 'string|in:pending,approved,rejected'
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'requete_ids.required' => 'La liste des requêtes est requise.',
            'requete_ids.array' => 'La liste des requêtes doit être un tableau.',
            'requete_ids.*.integer' => 'Chaque ID de requête doit être un entier.',
            'requete_ids.*.exists' => 'Une ou plusieurs requêtes sélectionnées n\'existent pas.',
            
            'status.string' => 'Le statut doit être une chaîne de caractères.',
            'status.in' => 'Le statut doit être "pending", "approved" ou "rejected".'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'requete_ids' => 'liste des requêtes',
            'requete_ids.*' => 'requête',
            'status' => 'statut'
        ];
    }
} 