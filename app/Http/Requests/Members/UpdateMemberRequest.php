<?php

namespace App\Http\Requests\Members;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
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
        $memberId = $this->route('id');
        
        return [
            'lastname' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('members', 'email')->ignore($memberId)
            ],
            'fonction' => 'required|string|max:255',
            'is_active' => 'nullable|boolean'
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
            'lastname.string' => 'Le nom de famille doit être une chaîne de caractères.',
            'lastname.max' => 'Le nom de famille ne peut pas dépasser 255 caractères.',
            
            'firstname.string' => 'Le prénom doit être une chaîne de caractères.',
            'firstname.max' => 'Le prénom ne peut pas dépasser 255 caractères.',
            
            'email.email' => 'L\'email doit être une adresse email valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            
            'fonction.string' => 'La fonction doit être une chaîne de caractères.',
            'fonction.max' => 'La fonction ne peut pas dépasser 255 caractères.',
            
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
            'lastname' => 'nom de famille',
            'firstname' => 'prénom',
            'email' => 'email',
            'fonction' => 'fonction',
            'is_active' => 'statut actif'
        ];
    }
} 