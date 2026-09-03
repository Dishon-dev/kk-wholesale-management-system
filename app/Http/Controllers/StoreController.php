<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreStoreRequest;
use App\Http\Requests\Api\V1\UpdateStoreRequest;
use App\Http\Resources\Api\V1\StoreResource;
use App\Models\Store;
use App\Services\StoreService;
use Illuminate\Http\Request;

class StoreController extends Controller
{
    public function __construct(
        protected StoreService $storeService
    ) {}

    public function index(Request $request)
    {
        $perPage = min(
            $request->integer('per_page', 20),
            100
        );

        $branchId = $request->integer('branch_id') ?: null;

        $stores = $this->storeService->paginate(
            $perPage,
            $branchId
        );

        return response()->json([
            'success' => true,
            'message' => 'Stores retrieved successfully.',
            'data' => StoreResource::collection(
                $stores->items()
            ),
            'meta' => [
                'current_page' => $stores->currentPage(),
                'last_page' => $stores->lastPage(),
                'per_page' => $stores->perPage(),
                'total' => $stores->total(),
            ],
        ]);
    }

    public function store(StoreStoreRequest $request)
    {
        $store = $this->storeService->create(
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Store created successfully.',
            'data' => new StoreResource($store),
        ], 201);
    }

    public function show(Store $store)
    {
        $store = $this->storeService->find($store);

        return response()->json([
            'success' => true,
            'message' => 'Store retrieved successfully.',
            'data' => new StoreResource($store),
        ]);
    }

    public function update(
        UpdateStoreRequest $request,
        Store $store
    ) {
        $store = $this->storeService->update(
            $store,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Store updated successfully.',
            'data' => new StoreResource($store),
        ]);
    }

    public function destroy(Store $store)
    {
        if ($store->users()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'This store cannot be deleted because it has users assigned to it.',
            ], 422);
        }

        $this->storeService->delete($store);

        return response()->json([
            'success' => true,
            'message' => 'Store deleted successfully.',
        ]);
    }
}
