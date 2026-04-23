<?php

namespace App\Http\Requests\Agenda;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAgendaRequest extends FormRequest
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
            'date_start' => 'required|date',
            'date_end' => 'nullable|date|after_or_equal:date_start',
            'title' => 'nullable|string|max:255',
            'status' => 'required|string',
            'description' => 'nullable|string',
            'usager_response' => 'nullable|boolean',
            'has_notif' => 'nullable|boolean',
            'user_id' => 'nullable|exists:users,id',
            'requete_id' => 'nullable|exists:requetes,id',
            'priority' => 'nullable|string|max:50',
            'ua_up' => 'nullable|integer',
            'from' => 'nullable|in:Usager,Métier',
            'session_type' => 'nullable|in:matinee,apres_midi,journee_entiere',
            'rdv_type' => 'nullable|in:premier_rdv,second_rdv,suivi_traitement',
            'duration_minutes' => 'nullable|integer|min:1',
            'max_slots' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'date_start.required' => 'La date de début est obligatoire.',
            'date_start.date' => 'La date de début doit être une date valide.',
            'date_end.date' => 'La date de fin doit être une date valide.',
            'date_end.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
            'status.required' => 'Le statut est obligatoire.',
            'user_id.exists' => 'L\'utilisateur sélectionné est invalide.',
            'requete_id.exists' => 'La requête sélectionnée est invalide.',
            'priority.max' => 'La priorité ne doit pas dépasser 50 caractères.',
            'session_type.in' => 'Le type de session doit être matinee, apres_midi ou journee_entiere.',
            'rdv_type.in' => 'Le type de RDV doit être premier_rdv, second_rdv ou suivi_traitement.',
            'from.in' => 'L\'origine doit être Usager ou Métier.',
        ];
    }
}
