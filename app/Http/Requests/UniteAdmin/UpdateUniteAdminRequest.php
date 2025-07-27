<?php

namespace App\Http\Requests\UniteAdmin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateUniteAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Ajustez selon vos besoins d'autorisation
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        $uniteAdminId = $this->route('unity_admin') ?? $this->route('id');

        return [
            'libelle' => [
                'sometimes',
                'required',
                'string',
                'max:255'
            ],
            'type_unite_admin_id' => [
                'sometimes',
                'required',
                'integer',
                'exists:type_unite_admins,id'
            ],
            'sigle' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                Rule::unique('unite_admins', 'sigle')->ignore($uniteAdminId)
            ],
            'email' => [
                'sometimes',
                'nullable',
                'email',
                'max:255',
                Rule::unique('unite_admins', 'email')->ignore($uniteAdminId)
            ],
            'entite_admin_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:entite_admins,id'
            ],
            'ua_parent_code' => [
                'sometimes',
                'nullable',
                'exists:unite_admins,id'
            ],
            'department_id' => [
                'sometimes',
                'nullable',
                'integer',
                'exists:departments,id'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Messages pour le code
            'code.required' => 'Le code est obligatoire.',
            'code.uuid' => 'Le code doit être un UUID valide.',
            'code.unique' => 'Ce code existe déjà dans le système.',

            // Messages pour le libellé
            'libelle.required' => 'Le libellé est obligatoire.',
            'libelle.string' => 'Le libellé doit être une chaîne de caractères.',
            'libelle.max' => 'Le libellé ne peut pas dépasser 255 caractères.',

            // Messages pour le type d'unité administrative
            'type_unite_admin_id.required' => 'Le type d\'unité administrative est obligatoire.',
            'type_unite_admin_id.integer' => 'Le type d\'unité administrative doit être un nombre entier.',
            'type_unite_admin_id.exists' => 'Le type d\'unité administrative sélectionné n\'existe pas.',

            // Messages pour le sigle
            'sigle.string' => 'Le sigle doit être une chaîne de caractères.',
            'sigle.max' => 'Le sigle ne peut pas dépasser 50 caractères.',
            'sigle.unique' => 'Ce sigle existe déjà dans le système.',

            // Messages pour l'email
            'email.email' => 'L\'adresse email doit être valide.',
            'email.max' => 'L\'adresse email ne peut pas dépasser 255 caractères.',
            'email.unique' => 'Cette adresse email existe déjà dans le système.',

            // Messages pour l'entité administrative
            'entite_admin_id.integer' => 'L\'entité administrative doit être un nombre entier.',
            'entite_admin_id.exists' => 'L\'entité administrative sélectionnée n\'existe pas.',

            // Messages pour l'unité administrative parente
            'ua_parent_code.uuid' => 'Le code de l\'unité administrative parente doit être un UUID valide.',
            'ua_parent_code.exists' => 'L\'unité administrative parente sélectionnée n\'existe pas.',

            // Messages pour le département
            'department_id.integer' => 'Le département doit être un nombre entier.',
            'department_id.exists' => 'Le département sélectionné n\'existe pas.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes()
    {
        return [
            'code' => 'code',
            'libelle' => 'libellé',
            'type_unite_admin_id' => 'type d\'unité administrative',
            'sigle' => 'sigle',
            'email' => 'adresse email',
            'entite_admin_id' => 'entité administrative',
            'ua_parent_code' => 'unité administrative parente',
            'department_id' => 'département'
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Supprimer les champs vides ou null pour permettre une mise à jour partielle
        $data = array_filter($this->all(), function ($value) {
            return $value !== null && $value !== '';
        });

        $this->merge($data);
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $uniteAdminId = $this->route('unite_admin') ?? $this->route('id');

            // Vérifier que l'unité parente n'est pas elle-même
            if ($this->ua_parent_code && $this->code && $this->ua_parent_code === $this->code) {
                $validator->errors()->add(
                    'ua_parent_code',
                    'Une unité administrative ne peut pas être sa propre parente.'
                );
            }

            // Vérifier qu'on ne crée pas une boucle hiérarchique
            if ($this->ua_parent_code && $uniteAdminId) {
                $this->validateHierarchy($validator, $uniteAdminId, $this->ua_parent_code);
            }
        });
    }

    /**
     * Valider la hiérarchie pour éviter les boucles.
     *
     * @param \Illuminate\Validation\Validator $validator
     * @param int $currentId
     * @param string $parentCode
     * @return void
     */
    private function validateHierarchy($validator, $currentId, $parentCode)
    {
        // Récupérer le code de l'unité courante
        $currentUnite = \App\Models\UniteAdmin::find($currentId);
        if (!$currentUnite) {
            return;
        }

        // Vérifier si le parent proposé est un descendant de l'unité courante
        $parent = \App\Models\UniteAdmin::where('code', $parentCode)->first();
        if ($parent) {
            $ancestors = $this->getAncestors($parent->code);
            if (in_array($currentUnite->code, $ancestors)) {
                $validator->errors()->add(
                    'ua_parent_code',
                    'L\'unité administrative parente ne peut pas être un descendant de l\'unité courante.'
                );
            }
        }
    }

    /**
     * Récupérer tous les ancêtres d'une unité administrative.
     *
     * @param string $code
     * @param array $visited
     * @return array
     */
    private function getAncestors($code, $visited = [])
    {
        if (in_array($code, $visited)) {
            return $visited; // Éviter les boucles infinies
        }

        $visited[] = $code;
        $unite = \App\Models\UniteAdmin::where('code', $code)->first();

        if ($unite && $unite->ua_parent_code) {
            return $this->getAncestors($unite->ua_parent_code, $visited);
        }

        return $visited;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Erreur de validation des données pour la mise à jour.',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}