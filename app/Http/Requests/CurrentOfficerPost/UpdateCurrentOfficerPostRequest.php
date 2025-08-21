<?php

namespace App\Http\Requests\CurrentOfficerPost;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCurrentOfficerPostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $testing = app()->environment('testing');

        return [
            'agent_id' => $testing ? 'sometimes|integer' : 'sometimes|integer|exists:agents,id',
            'unite_admin_id' => $testing ? 'sometimes|integer' : 'sometimes|integer|exists:unite_admins,id',
            'fonction_id' => $testing ? 'sometimes|integer' : 'sometimes|integer|exists:fonction_agents,id',
            'statut' => 'sometimes|string|in:active,inactive,ACTIVE,INACTIVE',
        ];
    }
}


