<?php

namespace App\Http\Requests\VisaCAFiles;

use Illuminate\Foundation\Http\FormRequest;

class StoreVisaCAFilesRequest extends FormRequest
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
            'type' => [
                'required',
                'string',
                'max:100',
                'in:document,justificatif,piece_jointe,rapport,attestation,certificat,facture,autre'
            ],
            'filename' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Z0-9\s\-_\.\(\)]+\.(pdf|doc|docx|xls|xlsx|jpg|jpeg|png)$/i'
            ],
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB max
                'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'
            ],
            'reference' => [
                'nullable',
                'string',
                'max:100'
            ],
            'visa_ca_id' => [
                'required',
                'integer',
                'exists:visa_c_a_s,id'
            ]
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Le type de fichier est obligatoire.',
            'type.string' => 'Le type de fichier doit être une chaîne de caractères.',
            'type.max' => 'Le type de fichier ne peut pas dépasser 100 caractères.',
            'type.in' => 'Le type de fichier sélectionné n\'est pas valide.',

            'filename.required' => 'Le nom du fichier est obligatoire.',
            'filename.string' => 'Le nom du fichier doit être une chaîne de caractères.',
            'filename.max' => 'Le nom du fichier ne peut pas dépasser 255 caractères.',
            'filename.regex' => 'Le nom du fichier contient des caractères non autorisés ou l\'extension n\'est pas supportée.',

            'file.required' => 'Le fichier est obligatoire.',
            'file.file' => 'Le fichier téléchargé n\'est pas valide.',
            'file.max' => 'Le fichier ne peut pas dépasser 10 MB.',
            'file.mimes' => 'Le fichier doit être de type: PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG ou PNG.',

            'reference.string' => 'La référence doit être une chaîne de caractères.',
            'reference.max' => 'La référence ne peut pas dépasser 100 caractères.',

            'visa_ca_id.required' => 'Le visa CAS est obligatoire.',
            'visa_ca_id.integer' => 'L\'identifiant du visa CAS doit être un nombre entier.',
            'visa_ca_id.exists' => 'Le visa CAS sélectionné n\'existe pas.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'type' => 'type de fichier',
            'filename' => 'nom du fichier',
            'file' => 'fichier',
            'reference' => 'référence',
            'visa_ca_id' => 'visa CAS'
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Si un fichier est téléchargé et que le filename n'est pas fourni, utiliser le nom du fichier
        if ($this->hasFile('file') && (!$this->has('filename') || empty($this->filename))) {
            $this->merge([
                'filename' => $this->file('file')->getClientOriginalName()
            ]);
        }

        // Nettoyer les données
        $this->merge([
            'type' => $this->type ? strtolower(trim($this->type)) : null,
            'filename' => $this->filename ? trim($this->filename) : null,
            'reference' => $this->reference ? trim($this->reference) : null,
        ]);
    }

    /**
     * Get the available file types.
     */
    public static function getFileTypes(): array
    {
        return [
            'document' => 'Document',
            'justificatif' => 'Justificatif',
            'piece_jointe' => 'Pièce jointe',
            'rapport' => 'Rapport',
            'attestation' => 'Attestation',
            'certificat' => 'Certificat',
            'facture' => 'Facture',
            'autre' => 'Autre'
        ];
    }
}
