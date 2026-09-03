<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreSaleReturnRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('returns.create');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'store_id' => [
                'required',
                'integer',
                'exists:stores,id',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_variant_id' => [
                'required',
                'integer',
                'exists:product_variants,id',
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'items.*.reason' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'reason' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }
}
