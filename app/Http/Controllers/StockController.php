<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AdjustStockRequest;
use App\Http\Requests\Api\V1\StoreOpeningStockRequest;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Store;
use App\Services\InventoryService;
use App\Services\StoreAccessService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService,
        protected StoreAccessService $storeAccessService
    ) {}
    
    public function index(Request $request)
    {
        $query = Stock::query()
            ->with([
                'store',
                'productVariant.product',
            ]);

        if ($request->filled('store_id')) {
            $query->where(
                'store_id',
                $request->integer('store_id')
            );
        }

        if ($request->filled('product_variant_id')) {
            $query->where(
                'product_variant_id',
                $request->integer('product_variant_id')
            );
        }

        $stocks = $query
            ->latest()
            ->paginate(
                min(
                    $request->integer('per_page', 20),
                    100
                )
            );

        return response()->json([
            'success' => true,
            'message' => 'Inventory retrieved successfully.',
            'data' => $stocks->items(),
            'meta' => [
                'current_page' =>
                    $stocks->currentPage(),
                'last_page' =>
                    $stocks->lastPage(),
                'per_page' =>
                    $stocks->perPage(),
                'total' =>
                    $stocks->total(),
            ],
        ]);
    }

    public function show(Stock $stock)
    {
        $stock->load([
            'store',
            'productVariant.product',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Inventory retrieved successfully.',
            'data' => $stock,
        ]);
    }

    public function opening(
        StoreOpeningStockRequest $request
    ) {
        $store = Store::findOrFail(
            $request->integer('store_id')
        );

        $this->storeAccessService
            ->ensureAccess($request->user(), $store);

        $variant = ProductVariant::findOrFail(
            $request->integer('product_variant_id')
        );

        $stock = $this->inventoryService->openingStock(
            $store,
            $variant,
            (float) $request->input('quantity'),
            $request->user(),
            $request->input('unit_cost'),
            $request->input('notes')
        );

        return response()->json([
            'success' => true,
            'message' => 'Opening stock saved successfully.',
            'data' => $stock,
        ]);
    }

    public function adjust(
        AdjustStockRequest $request
    ) {
        $store = Store::findOrFail(
            $request->integer('store_id')
        );

        $this->storeAccessService
            ->ensureAccess($request->user(), $store);

        $variant = ProductVariant::findOrFail(
            $request->integer('product_variant_id')
        );

        $stock = $this->inventoryService->adjust(
            $store,
            $variant,
            (float) $request->input('quantity'),
            $request->user(),
            $request->input('notes')
        );

        return response()->json([
            'success' => true,
            'message' => 'Inventory adjusted successfully.',
            'data' => $stock,
        ]);
    }
}
