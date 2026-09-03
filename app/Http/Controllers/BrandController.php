<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreBrandRequest;
use App\Http\Requests\Api\V1\UpdateBrandRequest;
use App\Http\Resources\Api\V1\BrandResource;
use App\Models\Brand;
use App\Services\BrandService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function __construct(
        protected BrandService $brandService
    ) {}

    public function index(Request $request)
    {
        $perPage = min(
            $request->integer('per_page', 20),
            100
        );

        $brands = $this->brandService->paginate(
            $perPage
        );

        return response()->json([
            'success' => true,
            'message' => 'Brands retrieved successfully.',
            'data' => BrandResource::collection(
                $brands->items()
            ),
            'meta' => [
                'current_page' => $brands->currentPage(),
                'last_page' => $brands->lastPage(),
                'per_page' => $brands->perPage(),
                'total' => $brands->total(),
            ],
        ]);
    }

    public function store(StoreBrandRequest $request)
    {
        $brand = $this->brandService->create(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Brand created successfully.',
            'data' => new BrandResource($brand),
        ], 201);
    }

    public function show(Brand $brand)
    {
        $brand = $this->brandService->find($brand);

        return response()->json([
            'success' => true,
            'message' => 'Brand retrieved successfully.',
            'data' => new BrandResource($brand),
        ]);
    }

    public function update(
        UpdateBrandRequest $request,
        Brand $brand
    ) {
        $brand = $this->brandService->update(
            $brand,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Brand updated successfully.',
            'data' => new BrandResource($brand),
        ]);
    }

    public function destroy(Brand $brand)
    {
        if ($brand->products()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This brand cannot be deleted because it has products assigned to it.',
            ], 422);
        }

        $this->brandService->delete($brand);

        return response()->json([
            'success' => true,
            'message' => 'Brand deleted successfully.',
        ]);
    }
}
