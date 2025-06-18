<?php

namespace App\Http\Requests\ValidationDesServicesAux;

use Illuminate\Foundation\Http\FormRequest;

class UpdateValidationDesServicesAuxRequest extends FormRequest
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
            'code' => 'required|uuid|unique:validation_des_service_auxes,code,' . $this->route('validation_service_aux'),
            'identity' => 'required|string|max:255',
            'sex' => 'required|string|in:Madame,Monsieur',
            'bride' => 'nullable|string|max:255',
            'birthplace' => 'nullable|string|max:255',
            'birthday' => 'nullable|date',
            'entity' => 'nullable|string|max:255',
            'job' => 'nullable|string|max:255',
            'rank' => 'nullable|string|max:255',
            'matricule' => 'nullable|string|max:255',
            'job_date' => 'nullable|date',
            'status' => 'nullable|string|max:255',
            'function' => 'nullable|string|max:255',
            'length_of_service' => 'nullable|string|max:255',
            'retirement_date' => 'nullable|date',
            'requete_id' => 'nullable|exists:requetes,id',
            'cnr_id' => 'nullable|exists:cnrs,id',
        ];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Le code est obligatoire.',
            'code.uuid' => 'Le format du code est invalide.',
            'code.unique' => 'Ce code est déjà utilisé pour une autre validation.',
            'identity.required' => 'Le nom est obligatoire.',
            'sex.required' => 'Le sexe est obligatoire.',
            'sex.in' => 'Le sexe doit être soit Madame soit Monsieur.',
            'birthday.date' => 'La date de naissance doit être une date valide.',
            'job_date.date' => 'La date d\'entrée en fonction doit être une date valide.',
            'retirement_date.date' => 'La date de départ à la retraite doit être une date valide.',
            'requete_id.exists' => 'La requête sélectionnée est invalide.',
            'cnr_id.exists' => 'Le CNR sélectionné est invalide.',
        ];
    }
}
