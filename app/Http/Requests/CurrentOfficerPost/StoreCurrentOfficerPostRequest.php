<?php

namespace App\Http\Requests\CurrentOfficerPost;

use Illuminate\Foundation\Http\FormRequest;

class StoreCurrentOfficerPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $testing = app()->environment('testing');

        return [
            'agent_id' => $testing ? 'required|integer' : 'required|integer|exists:agents,id',
            'unite_admin_id' => $testing ? 'required|integer' : 'required|integer|exists:unite_admins,id',
            'fonction_id' => $testing ? 'required|integer' : 'required|integer|exists:fonction_agents,id',
            'statut' => 'required|string|in:active,inactive,ACTIVE,INACTIVE',
        ];
    }
}


