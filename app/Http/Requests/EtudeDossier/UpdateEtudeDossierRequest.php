<?php

namespace App\Http\Requests\EtudeDossier;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEtudeDossierRequest extends FormRequest
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
            'mark' => 'nullable|numeric|min:0|max:20',
            'status' => 'nullable|string|in:pending,completed',
            'comment' => 'nullable|string|max:1000'
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
            'mark.numeric' => 'La note doit être un nombre.',
            'mark.min' => 'La note ne peut pas être inférieure à 0.',
            'mark.max' => 'La note ne peut pas dépasser 20.',
            
            'status.string' => 'Le statut doit être une chaîne de caractères.',
            'status.in' => 'Le statut doit être "pending" ou "completed".',
            
            'comment.string' => 'Le commentaire doit être une chaîne de caractères.',
            'comment.max' => 'Le commentaire ne peut pas dépasser 1000 caractères.'
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
            'mark' => 'note',
            'status' => 'statut',
            'comment' => 'commentaire'
        ];
    }
} 