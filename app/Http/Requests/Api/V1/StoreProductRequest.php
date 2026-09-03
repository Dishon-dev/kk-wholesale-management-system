<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (
            $this->filled('name')
            && ! $this->filled('slug')
        ) {
            $this->merge([
                'slug' => Str::slug(
                    $this->string('name')->toString()
                ),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => [
                'nullable',
                'integer',
                'exists:categories,id',
            ],

            'brand_id' => [
                'nullable',
                'integer',
                'exists:brands,id',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'required',
                'string',
                'max:255',
                'unique:products,slug',
            ],

            'short_description' => [
                'nullable',
                'string',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'currency' => [
                'sometimes',
                'string',
                'size:3',
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

            'dimensions_cm.length' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'dimensions_cm.width' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'dimensions_cm.height' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'gallery' => [
                'nullable',
                'array',
            ],

            'gallery.*' => [
                'string',
                'max:2048',
            ],

            'is_featured' => [
                'sometimes',
                'boolean',
            ],

            'is_bestseller' => [
                'sometimes',
                'boolean',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],

            'meta_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'meta_description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'options' => [
                'nullable',
                'array',
            ],

            'options.*.name' => [
                'required',
                'string',
                'max:100',
            ],

            'options.*.slug' => [
                'nullable',
                'string',
                'max:100',
            ],

            'options.*.sort_order' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'options.*.is_required' => [
                'sometimes',
                'boolean',
            ],

            'options.*.values' => [
                'required',
                'array',
                'min:1',
            ],

            'options.*.values.*.value' => [
                'required',
                'string',
                'max:100',
            ],

            'options.*.values.*.slug' => [
                'nullable',
                'string',
                'max:100',
            ],

            'options.*.values.*.sort_order' => [
                'sometimes',
                'integer',
                'min:0',
            ],

            'variants' => [
                'nullable',
                'array',
                'min:1',
            ],

            'variants.*.name' => [
                'required',
                'string',
                'max:255',
            ],

            'variants.*.sku' => [
                'required',
                'string',
                'max:255',
                'distinct',
                'unique:product_variants,sku',
            ],

            'variants.*.barcode' => [
                'nullable',
                'string',
                'max:255',
                'distinct',
                'unique:product_variants,barcode',
            ],

            'variants.*.price' => [
                'required',
                'numeric',
                'min:0',
            ],

            'variants.*.cost' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'variants.*.weight_kg' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'variants.*.dimensions_cm' => [
                'nullable',
                'array',
            ],

            'variants.*.track_stock' => [
                'sometimes',
                'boolean',
            ],

            'variants.*.is_default' => [
                'sometimes',
                'boolean',
            ],

            'variants.*.is_active' => [
                'sometimes',
                'boolean',
            ],

            'variants.*.option_value_ids' => [
                'nullable',
                'array',
            ],

            'variants.*.option_value_ids.*' => [
                'integer',
                'distinct',
                'exists:product_option_values,id',
            ],
        ];
    }
}
