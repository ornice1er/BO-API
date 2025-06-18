<?php

namespace App\Http\Requests\Agent;

use Illuminate\Foundation\Http\FormRequest;

class StoreAgentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'code' => 'required|uuid|unique:agents,code',
            'lastname' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'numero_matricule' => 'required|string|unique:agents,numero_matricule',
            'unite_admin_id' => 'required|exists:unite_admins,id',
            'entite_admin_id' => 'required|exists:entite_admins,id',
            'fonction_agent_id' => 'required|exists:fonction_agents,id',
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'Le code est obligatoire.',
            'code.uuid' => 'Le code doit être au format UUID.',
            'code.unique' => 'Ce code existe déjà.',
            'lastname.required' => 'Le nom est obligatoire.',
            'firstname.required' => 'Le prénom est obligatoire.',
            'numero_matricule.required' => 'Le numéro matricule est obligatoire.',
            'numero_matricule.unique' => 'Ce numéro matricule est déjà utilisé.',
            'unite_admin_id.required' => 'L’unité administrative est obligatoire.',
            'unite_admin_id.exists' => 'L’unité administrative sélectionnée est invalide.',
            'entite_admin_id.required' => 'L’entité administrative est obligatoire.',
            'entite_admin_id.exists' => 'L’entité administrative sélectionnée est invalide.',
            'fonction_agent_id.required' => 'La fonction de l’agent est obligatoire.',
            'fonction_agent_id.exists' => 'La fonction sélectionnée est invalide.',
        ];
    }
}
