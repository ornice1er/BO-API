<?php

namespace App\Http\Requests\Commission;

use Illuminate\Foundation\Http\FormRequest;

class AddMembersToCommissionRequest extends FormRequest
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
            'member_ids' => 'required|array',
            'member_ids.*' => 'integer|exists:members,id',
            'is_active' => 'boolean'
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
            'member_ids.required' => 'La liste des membres est requise.',
            'member_ids.array' => 'La liste des membres doit être un tableau.',
            'member_ids.*.integer' => 'Chaque ID de membre doit être un entier.',
            'member_ids.*.exists' => 'Un ou plusieurs membres sélectionnés n\'existent pas.',
            
            'is_active.boolean' => 'Le statut actif doit être vrai ou faux.'
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
            'member_ids' => 'liste des membres',
            'member_ids.*' => 'membre',
            'is_active' => 'statut actif'
        ];
    }
} 