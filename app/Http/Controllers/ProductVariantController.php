<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreProductVariantRequest;
use App\Http\Requests\Api\V1\UpdateProductVariantRequest;
use App\Http\Resources\Api\V1\ProductVariantResource;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductVariantService;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function __construct(
        protected ProductVariantService $variantService
    ) {}

    public function index(
        Request $request,
        Product $product
    ) {
        $perPage = min(
            $request->integer('per_page', 20),
            100
        );

        $variants = $this->variantService->paginate(
            $product,
            $perPage
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Product variants retrieved successfully.',
            'data' => ProductVariantResource::collection(
                $variants->items()
            ),
            'meta' => [
                'current_page' =>
                    $variants->currentPage(),
                'last_page' =>
                    $variants->lastPage(),
                'per_page' =>
                    $variants->perPage(),
                'total' =>
                    $variants->total(),
            ],
        ]);
    }

    public function store(
        StoreProductVariantRequest $request,
        Product $product
    ) {
        $variant = $this->variantService->create(
            $product,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Product variant created successfully.',
            'data' => new ProductVariantResource(
                $variant
            ),
        ], 201);
    }

    public function show(
        Product $product,
        ProductVariant $variant
    ) {
        $this->ensureVariantBelongsToProduct(
            $product,
            $variant
        );

        $variant = $this->variantService->find(
            $product,
            $variant
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Product variant retrieved successfully.',
            'data' => new ProductVariantResource(
                $variant
            ),
        ]);
    }

    public function update(
        UpdateProductVariantRequest $request,
        Product $product,
        ProductVariant $variant
    ) {
        $this->ensureVariantBelongsToProduct(
            $product,
            $variant
        );

        $variant = $this->variantService->update(
            $product,
            $variant,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Product variant updated successfully.',
            'data' => new ProductVariantResource(
                $variant
            ),
        ]);
    }

    public function destroy(
        Product $product,
        ProductVariant $variant
    ) {
        $this->ensureVariantBelongsToProduct(
            $product,
            $variant
        );

        $this->variantService->delete(
            $product,
            $variant
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Product variant deleted successfully.',
        ]);
    }

    protected function ensureVariantBelongsToProduct(
        Product $product,
        ProductVariant $variant
    ): void {
        if ($variant->product_id !== $product->id) {
            abort(
                404,
                'Product variant not found.'
            );
        }
    }
}