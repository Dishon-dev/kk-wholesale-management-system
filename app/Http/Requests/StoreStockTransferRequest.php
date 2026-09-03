<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockTransferRequest extends FormRequest
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
        return [
            'from_store_id' => [
                'required',
                'integer',
                'exists:stores,id',
            ],

            'to_store_id' => [
                'required',
                'integer',
                'exists:stores,id',
                'different:from_store_id',
            ],

            'transfer_date' => [
                'nullable',
                'date',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_variant_id' => [
                'required',
                'integer',
                'distinct',
                'exists:product_variants,id',
            ],

            'items.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'to_store_id.different' =>
                'The destination store must be different from the source store.',

            'items.min' =>
                'At least one product variant is required.',

            'items.*.quantity.gt' =>
                'Transfer quantity must be greater than zero.',
        ];
    }
}
