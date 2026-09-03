<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoreRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $store = $this->route('store');

        return [
            'branch_id' => [
                'sometimes',
                'integer',
                'exists:branches,id',
            ],

            'name' => [
                'sometimes',
                'string',
                'max:255',
            ],

            'store_code' => [
                'sometimes',
                'string',
                'max:50',
                Rule::unique('stores', 'store_code')
                    ->ignore($store?->id),
            ],

            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'address' => [
                'nullable',
                'string',
                'max:500',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }
}
