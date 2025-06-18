<?php

namespace App\Http\Requests\Agent;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAgentRequest extends FormRequest
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
        $agentId = $this->route('agent');

        return [
            'code' => "required|uuid|unique:agents,code,{$agentId}",
            'lastname' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'numero_matricule' => "required|string|unique:agents,numero_matricule,{$agentId}",
            'unite_admin_id' => 'required|exists:unite_admins,id',
            'entite_admin_id' => 'required|exists:entite_admins,id',
            'fonction_agent_id' => 'required|exists:fonction_agents,id',
        ];
    }

    public function messages()
    {
        return (new StoreAgentRequest())->messages(); // Réutilise les mêmes messages
    }
}
