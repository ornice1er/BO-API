<?php

namespace App\Http\Requests\Prestation;

use Illuminate\Foundation\Http\FormRequest;

class StorePrestationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'required|string|unique:prestations,code',
            'name' => 'required|string|max:255',
           // 'slug' => 'required|string|unique:prestations,slug',
            'needOut' => 'required|boolean',
            'unite_admin_id' => 'nullable|integer|exists:unite_admins,id',
            'entite_admin_id' => 'nullable|integer|exists:entite_admins,id',
            'desc' => 'nullable|string',
            'content_type' => 'required|integer',
            'need_meeting' => 'required|boolean',
            'need_validation' => 'required|boolean',
            'signer' => 'required',
            'start_point' => 'required|integer|min:0',
            'delay' => 'required|integer|min:0',
            'is_automatic_delivered' => 'required|boolean',
            'from_pns' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Le code est obligatoire.',
            'code.unique' => 'Ce code existe déjà.',
            'name.required' => 'Le nom de la prestation est obligatoire.',
            'slug.required' => 'Le slug est obligatoire.',
            'slug.unique' => 'Ce slug est déjà utilisé.',
            'needOut.required' => 'Le champ needOut est requis.',
            'unite_admin_id.exists' => 'L’unité administrative sélectionnée est invalide.',
            'entite_admin_id.exists' => 'L’entité administrative sélectionnée est invalide.',
            'content_type.required' => 'Le type de contenu est requis.',
            'need_meeting.required' => 'Veuillez indiquer si une réunion est nécessaire.',
            'need_validation.required' => 'Veuillez indiquer si une validation est nécessaire.',
            'signer.required' => 'Veuillez indiquer si une signature est nécessaire.',
            'start_point.required' => 'Le point de départ est requis.',
            'delay.required' => 'Le délai est requis.',
            'is_automatic_delivered.required' => 'Veuillez indiquer si la livraison est automatique.',
            'from_pns.required' => 'Veuillez indiquer si cela provient du PNS.',
        ];
    }

    protected function prepareForValidation()
{
    $this->merge([
        'from_pns' => $this->has('from_pns')
            ? filter_var($this->input('from_pns'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)
            : false,
    ]);
}
}
