<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        $query = StockMovement::query()
            ->with([
                'store',
                'productVariant.product',
                'createdBy',
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

        if ($request->filled('type')) {
            $query->where(
                'type',
                $request->string('type')->toString()
            );
        }

        $movements = $query
            ->latest()
            ->paginate(
                min(
                    $request->integer('per_page', 20),
                    100
                )
            );

        return response()->json([
            'success' => true,
            'message' =>
                'Stock movements retrieved successfully.',
            'data' => $movements->items(),
            'meta' => [
                'current_page' =>
                    $movements->currentPage(),
                'last_page' =>
                    $movements->lastPage(),
                'per_page' =>
                    $movements->perPage(),
                'total' =>
                    $movements->total(),
            ],
        ]);
    }

    public function show(
        StockMovement $stockMovement
    ) {
        $stockMovement->load([
            'store',
            'productVariant.product',
            'createdBy',
            'reference',
        ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Stock movement retrieved successfully.',
            'data' => $stockMovement,
        ]);
    }
}
