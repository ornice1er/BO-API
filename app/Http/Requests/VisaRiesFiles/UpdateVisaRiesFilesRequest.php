<?php

namespace App\Http\Requests\VisaRiesFiles;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVisaRiesFilesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Ajustez selon vos besoins d'autorisation
    }

    /**
     * Règles de validation pour la mise à jour d'un fichier visa.
     */
    public function rules(): array
    {
        return [
            'type' => 'sometimes|required|string|max:255',
            'filename' => 'sometimes|required|string|max:255',
            'reference' => 'nullable|string|max:255',
            'visa_rie_id' => 'sometimes|required|integer|exists:visa_r_i_e_s,id',
            'file' => 'sometimes|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB max
        ];
    }

    /**
     * Messages d'erreur personnalisés en français.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Le type de fichier est obligatoire.',
            'type.string' => 'Le type de fichier doit être une chaîne de caractères.',
            'type.max' => 'Le type de fichier ne peut pas dépasser 255 caractères.',

            'filename.required' => 'Le nom du fichier est obligatoire.',
            'filename.string' => 'Le nom du fichier doit être une chaîne de caractères.',
            'filename.max' => 'Le nom du fichier ne peut pas dépasser 255 caractères.',

            'reference.string' => 'La référence doit être une chaîne de caractères.',
            'reference.max' => 'La référence ne peut pas dépasser 255 caractères.',

            'visa_rie_id.required' => 'L\'identifiant du visa RIE est obligatoire.',
            'visa_rie_id.integer' => 'L\'identifiant du visa RIE doit être un nombre entier.',
            'visa_rie_id.exists' => 'Le visa RIE sélectionné n\'existe pas.',

            'file.file' => 'Le fichier téléchargé n\'est pas valide.',
            'file.mimes' => 'Le fichier doit être au format : pdf, doc, docx, jpg, jpeg, png.',
            'file.max' => 'Le fichier ne peut pas dépasser 10 MB.',
        ];
    }

    /**
     * Noms d'attributs personnalisés pour les messages d'erreur.
     */
    public function attributes(): array
    {
        return [
            'type' => 'type de fichier',
            'filename' => 'nom du fichier',
            'reference' => 'référence',
            'visa_rie_id' => 'visa RIE',
            'file' => 'fichier',
        ];
    }

    /**
     * Prépare les données pour la validation.
     */
    protected function prepareForValidation(): void
    {
        // Supprimer les champs vides pour éviter la validation inutile
        $this->merge(
            array_filter($this->all(), function ($value) {
                return $value !== null && $value !== '';
            })
        );
    }
}
