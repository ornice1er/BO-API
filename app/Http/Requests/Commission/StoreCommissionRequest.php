<?php

namespace App\Http\Requests\Commission;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommissionRequest extends FormRequest
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
            'arrete_file' => 'nullable|string|max:255',
            'decret_file' => 'nullable|string|max:255',
            'status' => 'string|in:active,closed',
            'responsable' => 'required|string|max:255'
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
            'name.required' => 'Le nom de la commission est requis.',
            'name.string' => 'Le nom de la commission doit être une chaîne de caractères.',
            'name.max' => 'Le nom de la commission ne peut pas dépasser 255 caractères.',
            
            'arrete_file.string' => 'Le fichier d\'arrêté doit être une chaîne de caractères.',
            'arrete_file.max' => 'Le nom du fichier d\'arrêté ne peut pas dépasser 255 caractères.',
            
            'decret_file.string' => 'Le fichier de décret doit être une chaîne de caractères.',
            'decret_file.max' => 'Le nom du fichier de décret ne peut pas dépasser 255 caractères.',
            
            'status.string' => 'Le statut doit être une chaîne de caractères.',
            'status.in' => 'Le statut doit être "active" ou "closed".',
            
            'responsable.required' => 'Le responsable est requis.',
            'responsable.string' => 'Le responsable doit être une chaîne de caractères.',
            'responsable.max' => 'Le nom du responsable ne peut pas dépasser 255 caractères.'
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
            'name' => 'nom de la commission',
            'arrete_file' => 'fichier d\'arrêté',
            'decret_file' => 'fichier de décret',
            'status' => 'statut',
            'responsable' => 'responsable'
        ];
    }
} 