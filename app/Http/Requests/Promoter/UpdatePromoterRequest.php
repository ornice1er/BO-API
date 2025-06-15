<?php
namespace App\Http\Requests\Promoter;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePromoterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255|unique:promoters,name,' . $this->route('promoter'),
            'contact' => 'required|string|max:255',
            'status' => 'required|in:Active,Inactive',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Le nom du promoteur est requis.',
            'contact.required' => 'Les informations de contact sont requises.',
            'status.required' => 'Le statut est requis.',
            'name.unique' => 'Ce nom de promoteur existe déjà.',
        ];
    }
}
