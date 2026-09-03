<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreProductOptionRequest;
use App\Http\Requests\Api\V1\UpdateProductOptionRequest;
use App\Http\Resources\Api\V1\ProductOptionResource;
use App\Models\Product;
use App\Models\ProductOption;
use App\Services\ProductOptionService;

class ProductOptionController extends Controller
{
    public function __construct(
        protected ProductOptionService $optionService
    ) {}

    public function index(Product $product)
    {
        $options = $this->optionService->index(
            $product
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Product options retrieved successfully.',
            'data' => ProductOptionResource::collection(
                $options
            ),
        ]);
    }

    public function store(
        StoreProductOptionRequest $request,
        Product $product
    ) {
        $option = $this->optionService->create(
            $product,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Product option created successfully.',
            'data' => new ProductOptionResource(
                $option
            ),
        ], 201);
    }

    public function show(
        Product $product,
        ProductOption $option
    ) {
        $this->ensureBelongsToProduct(
            $product,
            $option
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Product option retrieved successfully.',
            'data' => new ProductOptionResource(
                $option->load('values')
            ),
        ]);
    }

    public function update(
        UpdateProductOptionRequest $request,
        Product $product,
        ProductOption $option
    ) {
        $this->ensureBelongsToProduct(
            $product,
            $option
        );

        $option = $this->optionService->update(
            $option,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Product option updated successfully.',
            'data' => new ProductOptionResource(
                $option
            ),
        ]);
    }

    public function destroy(
        Product $product,
        ProductOption $option
    ) {
        $this->ensureBelongsToProduct(
            $product,
            $option
        );

        $this->optionService->delete($option);

        return response()->json([
            'success' => true,
            'message' =>
                'Product option deleted successfully.',
        ]);
    }

    protected function ensureBelongsToProduct(
        Product $product,
        ProductOption $option
    ): void {
        if ($option->product_id !== $product->id) {
            abort(404, 'Product option not found.');
        }
    }
}
