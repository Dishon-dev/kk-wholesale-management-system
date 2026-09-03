<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreProductRequest;
use App\Http\Requests\Api\V1\UpdateProductRequest;
use App\Http\Resources\Api\V1\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $productService
    ) {}

    public function index(Request $request)
    {
        $perPage = min(
            $request->integer('per_page', 20),
            100
        );

        $categoryId =
            $request->integer('category_id')
            ?: null;

        $brandId =
            $request->integer('brand_id')
            ?: null;

        $search =
            $request->string('search')->toString()
            ?: null;

        $products = $this->productService->paginate(
            $perPage,
            $categoryId,
            $brandId,
            $search
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Products retrieved successfully.',
            'data' => ProductResource::collection(
                $products->items()
            ),
            'meta' => [
                'current_page' =>
                    $products->currentPage(),
                'last_page' =>
                    $products->lastPage(),
                'per_page' =>
                    $products->perPage(),
                'total' =>
                    $products->total(),
            ],
        ]);
    }

    public function store(
        StoreProductRequest $request
    ) {
        $product = $this->productService->create(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Product created successfully.',
            'data' => new ProductResource($product),
        ], 201);
    }

    public function show(Product $product)
    {
        $product = $this->productService->find(
            $product
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Product retrieved successfully.',
            'data' => new ProductResource($product),
        ]);
    }

    public function update(
        UpdateProductRequest $request,
        Product $product
    ) {
        $product = $this->productService->update(
            $product,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Product updated successfully.',
            'data' => new ProductResource($product),
        ]);
    }

    public function destroy(Product $product)
    {
        if ($product->variants()->exists()) {
            return response()->json([
                'success' => false,
                'message' =>
                    'This product cannot be deleted because it has variants. Deactivate the product instead.',
            ], 422);
        }

        $this->productService->delete($product);

        return response()->json([
            'success' => true,
            'message' =>
                'Product deleted successfully.',
        ]);
    }
}