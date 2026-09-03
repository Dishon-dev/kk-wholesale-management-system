<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStockTransferRequest;
use App\Models\StockTransfer;
use App\Services\StockTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class StockTransferController extends Controller
{
    public function __construct(
        protected StockTransferService $stockTransferService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = StockTransfer::query()
            ->with([
                'fromStore',
                'toStore',
                'createdBy',
                'items.productVariant.product',
            ])
            ->latest('transfer_date');

        if (! $user->hasRole('Super Admin')) {
            $storeId = $user->store_id;

            $query->where(function ($query) use ($storeId) {
                $query
                    ->where('from_store_id', $storeId)
                    ->orWhere('to_store_id', $storeId);
            });
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->string('status')
            );
        }

        if ($request->filled('from_store_id')) {
            $query->where(
                'from_store_id',
                $request->integer('from_store_id')
            );
        }

        if ($request->filled('to_store_id')) {
            $query->where(
                'to_store_id',
                $request->integer('to_store_id')
            );
        }

        $transfers = $query->paginate(
            $request->integer('per_page', 20)
        );

        return response()->json([
            'success' => true,
            'message' => 'Stock transfers retrieved successfully.',
            'data' => $transfers->items(),
            'meta' => [
                'current_page' => $transfers->currentPage(),
                'last_page' => $transfers->lastPage(),
                'per_page' => $transfers->perPage(),
                'total' => $transfers->total(),
            ],
        ]);
    }

    public function store(
        StoreStockTransferRequest $request
    ): JsonResponse {
        try {
            $transfer = $this->stockTransferService->create(
                $request->user(),
                $request->validated()
            );

            return response()->json([
                'success' => true,
                'message' => 'Stock transfer completed successfully.',
                'data' => $transfer,
            ], 201);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    public function show(
        Request $request,
        StockTransfer $stockTransfer
    ): JsonResponse {
        $user = $request->user();

        if (
            ! $user->hasRole('Super Admin') &&
            $user->store_id !== $stockTransfer->from_store_id &&
            $user->store_id !== $stockTransfer->to_store_id
        ) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this stock transfer.',
            ], 403);
        }

        $stockTransfer->load([
            'fromStore',
            'toStore',
            'createdBy',
            'items.productVariant.product',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock transfer retrieved successfully.',
            'data' => $stockTransfer,
        ]);
    }
}
