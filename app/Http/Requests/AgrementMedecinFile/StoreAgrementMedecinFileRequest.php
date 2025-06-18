<?php

namespace App\Http\Requests\AgrementMedecinFile;

use Illuminate\Foundation\Http\FormRequest;

class StoreAgrementMedecinFileRequest extends FormRequest
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
            'type' => 'required|string|in:PDF,JPG,PNG',
            'filename' => 'required|string|max:255',
            'reference' => 'nullable|string|max:255',
            'vague' => 'required|string|max:100',
            'am_id' => 'required|exists:agrement_medecins,id',
        ];
    }

    public function messages()
    {
        return [
            'type.required' => 'Le type de fichier est obligatoire.',
            'type.in' => 'Le type doit être l’un des suivants : PDF, JPG ou PNG.',
            'filename.required' => 'Le nom du fichier est obligatoire.',
            'filename.max' => 'Le nom du fichier est trop long.',
            'reference.max' => 'La référence est trop longue.',
            'vague.required' => 'La vague est obligatoire.',
            'am_id.required' => 'L’identifiant du médecin est obligatoire.',
            'am_id.exists' => 'Le médecin spécifié n’existe pas.',
        ];
    }
}
