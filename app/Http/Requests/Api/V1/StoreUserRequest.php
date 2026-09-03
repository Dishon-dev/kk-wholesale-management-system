<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('users.create');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::defaults(),
            ],

            'branch_id' => [
                'nullable',
                'integer',
                'exists:branches,id',
            ],

            'store_id' => [
                'nullable',
                'integer',
                'exists:stores,id',
            ],

            'store_ids' => [
                'nullable',
                'array',
            ],

            'store_ids.*' => [
                'integer',
                'exists:stores,id',
                'distinct',
            ],

            'role' => [
                'required',
                'string',
                'exists:roles,name',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
