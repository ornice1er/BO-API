<?php

namespace App\Http\Requests\AgrementMedecinFile;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAgrementMedecinFileRequest extends FormRequest
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
        return (new StoreAgrementMedecinFileRequest)->messages(); // Réutilisation
    }
}
