<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreProductOptionValueRequest;
use App\Http\Requests\Api\V1\UpdateProductOptionValueRequest;
use App\Http\Resources\Api\V1\ProductOptionValueResource;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Services\ProductOptionValueService;

class ProductOptionValueController extends Controller
{
    public function __construct(
        protected ProductOptionValueService $valueService
    ) {}

    public function index(
        Product $product,
        ProductOption $option
    ) {
        $this->ensureBelongsToProduct(
            $product,
            $option
        );

        $values = $this->valueService->index(
            $option
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Product option values retrieved successfully.',
            'data' => ProductOptionValueResource::collection(
                $values
            ),
        ]);
    }

    public function store(
        StoreProductOptionValueRequest $request,
        Product $product,
        ProductOption $option
    ) {
        $this->ensureBelongsToProduct(
            $product,
            $option
        );

        $value = $this->valueService->create(
            $option,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Product option value created successfully.',
            'data' => new ProductOptionValueResource(
                $value
            ),
        ], 201);
    }

    public function show(
        Product $product,
        ProductOption $option,
        ProductOptionValue $value
    ) {
        $this->ensureBelongsToProduct(
            $product,
            $option
        );

        $this->ensureValueBelongsToOption(
            $option,
            $value
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Product option value retrieved successfully.',
            'data' => new ProductOptionValueResource(
                $value
            ),
        ]);
    }

    public function update(
        UpdateProductOptionValueRequest $request,
        Product $product,
        ProductOption $option,
        ProductOptionValue $value
    ) {
        $this->ensureBelongsToProduct(
            $product,
            $option
        );

        $this->ensureValueBelongsToOption(
            $option,
            $value
        );

        $value = $this->valueService->update(
            $value,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Product option value updated successfully.',
            'data' => new ProductOptionValueResource(
                $value
            ),
        ]);
    }

    public function destroy(
        Product $product,
        ProductOption $option,
        ProductOptionValue $value
    ) {
        $this->ensureBelongsToProduct(
            $product,
            $option
        );

        $this->ensureValueBelongsToOption(
            $option,
            $value
        );

        $this->valueService->delete($value);

        return response()->json([
            'success' => true,
            'message' =>
                'Product option value deleted successfully.',
        ]);
    }

    protected function ensureBelongsToProduct(
        Product $product,
        ProductOption $option
    ): void {
        if ($option->product_id !== $product->id) {
            abort(
                404,
                'Product option not found.'
            );
        }
    }

    protected function ensureValueBelongsToOption(
        ProductOption $option,
        ProductOptionValue $value
    ): void {
        if (
            $value->product_option_id
            !== $option->id
        ) {
            abort(
                404,
                'Product option value not found.'
            );
        }
    }
}
