<?php

namespace App\Http\Requests\Permission;

use App\Utilities\Common;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdatePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $permissionId = $this->route('permission') instanceof \App\Models\Permission
        ? $this->route('permission')->id
        : $this->route('permission');
        //  'name' => 'sometimes|string|max:255|unique:permissions,name,' . $permissionId . ',id',

        return [
            'name' => 'sometimes|string|max:255',
            'guard_name' => 'required|string|max:255',
            'show_edit' => 'required|boolean',
            'show_only' => 'required|boolean',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(Common::error($validator->errors()->first(), $validator->errors()));
    }

    public function messages()
    {
        return [
            'name.string' => 'Le nom doit être une chaîne de caractères.',
            'name.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'name.unique' => 'Cette permission existe déjà.',
            'guard_name.string' => 'Le guard_name doit être une chaîne de caractères.',
            'guard_name.string' => 'Le guard_name doit être une chaîne de caractères.',
            'guard_name.max' => 'Le guard_name ne doit pas dépasser 255 caractères.',
            'group_name.required' => 'Le group_name est requis.',
            'group_name.string' => 'Le group_name doit être une chaîne de caractères.',
            'group_name.max' => 'Le group_name ne doit pas dépasser 255 caractères.',
            'show_edit.required' => 'L\'action voir et modifier est requise.',
            'show_edit.boolean' => 'L\'action voir et modifier est doit être un boolean.',
            'show_only.required' => 'L\'action voir uniquement est requise.',
            'show_only.boolean' => 'L\'action voir uniquement est doit être un boolean.',
        ];
    }
}
