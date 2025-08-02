<?php

namespace App\Http\Requests\EtudeDossier;

use Illuminate\Foundation\Http\FormRequest;

class StoreEtudeDossierRequest extends FormRequest
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
            'commission_member_id' => 'required|integer|exists:commission_members,id',
            'commission_requete_id' => 'required|integer|exists:commission_requetes,id',
            'mark' => 'nullable|numeric|min:0|max:20',
            'status' => 'string|in:pending,completed',
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
            'commission_member_id.required' => 'L\'ID du membre de commission est requis.',
            'commission_member_id.integer' => 'L\'ID du membre de commission doit être un entier.',
            'commission_member_id.exists' => 'Le membre de commission sélectionné n\'existe pas.',
            
            'commission_requete_id.required' => 'L\'ID de la commission-requête est requis.',
            'commission_requete_id.integer' => 'L\'ID de la commission-requête doit être un entier.',
            'commission_requete_id.exists' => 'La commission-requête sélectionnée n\'existe pas.',
            
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
            'commission_member_id' => 'membre de commission',
            'commission_requete_id' => 'commission-requête',
            'mark' => 'note',
            'status' => 'statut',
            'comment' => 'commentaire'
        ];
    }
} 