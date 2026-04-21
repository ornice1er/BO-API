<?php

namespace App\Http\Requests\Workflow;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreWorkflowRequest extends FormRequest
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
            'prestation_id'      => 'required|integer|exists:prestations,id',
            'etape_from_id'      => 'required|integer|exists:etapes,id',
            'etape_to_id'        => 'nullable|integer|exists:etapes,id',
            'condition_type'     => 'required|in:auto,validation,rejet,complement,signature,cloture,paraphe,prevalidation,choix_sortie',
            'status_result_id'   => [
                'required', 'integer', 'exists:statuses,id',
                \Illuminate\Validation\Rule::exists('prestation_statuses', 'status_id')
                    ->where('prestation_id', $this->prestation_id),
            ],
            'order'              => 'nullable|integer|min:0',
            'notify_requérant'   => 'nullable|boolean',
            'notify_agent'       => 'nullable|boolean',
            'is_active'          => 'nullable|boolean',
            'decision'          => 'nullable|string|max:255',
            'can_act_pns'       => 'nullable|boolean',
        ];
    }

    /**
     * Informations à afficher au cas où il y aurait des erreurs de validation
     */
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    /**
     * Mettre les messages d'erreur en Français
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Le nom du projet est requis.',
            'name.string' => 'Le nom du projet doit être une chaîne de caractères.',
            'name.max' => 'Le nom du projet ne doit pas dépasser 255 caractères.',
            'description.required' => 'La description du projet est requise.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.max' => 'La description ne doit pas dépasser 1000 caractères.',
            'pc_id.required' => 'La catégorie du projet est requise.',
            'pc_id.exists' => 'La catégorie du projet  n\'existe pas.',
            'pc_id.integer' => 'L\'id de Le département n\'est pas un entier .',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'notify_requérant' => $this->notify_requérant == null ? false : $this->boolean('notify_requérant'),
            'notify_agent' => $this->notify_agent==null ? false : $this->boolean('notify_agent'),
            'is_active' => $this->is_active==null ? false : $this->boolean('is_active'),
        ]);
        // Custom preparation logic if needed
    }
}
