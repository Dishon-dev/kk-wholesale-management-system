<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductVariantRequest extends FormRequest
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

            'sku' => [
                'required',
                'string',
                'max:255',
                'unique:product_variants,sku',
            ],

            'barcode' => [
                'nullable',
                'string',
                'max:255',
                'unique:product_variants,barcode',
            ],

            'price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'weight_kg' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'dimensions_cm' => [
                'nullable',
                'array',
            ],

            'track_stock' => [
                'sometimes',
                'boolean',
            ],

            'is_default' => [
                'sometimes',
                'boolean',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],

            'option_value_ids' => [
                'nullable',
                'array',
            ],

            'option_value_ids.*' => [
                'integer',
                'distinct',
                'exists:product_option_values,id',
            ],
        ];
    }
}
