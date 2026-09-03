<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\SaleReturn;
use App\Models\Stock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function sales(Request $request): JsonResponse
    {
        $query = Sale::query()
            ->with(['store', 'customer', 'createdBy'])
            ->where('status', 'completed');

        if (
            ! $request->user()->hasRole('Super Admin')
        ) {
            $query->where(
                'store_id',
                $request->user()->store_id
            );
        }

        if ($request->filled('store_id')) {
            $query->where(
                'store_id',
                $request->integer('store_id')
            );
        }

        if ($request->filled('from')) {
            $query->whereDate(
                'sale_date',
                '>=',
                $request->input('from')
            );
        }

        if ($request->filled('to')) {
            $query->whereDate(
                'sale_date',
                '<=',
                $request->input('to')
            );
        }

        $sales = $query
            ->latest('sale_date')
            ->paginate(
                $request->integer('per_page', 50)
            );

        return response()->json([
            'success' => true,
            'message' => 'Sales report retrieved successfully.',
            'data' => SaleResource::collection(
                $sales->items()
            )->resolve(),
            'meta' => [
                'current_page' => $sales->currentPage(),
                'last_page' => $sales->lastPage(),
                'per_page' => $sales->perPage(),
                'total' => $sales->total(),
            ],
        ]);  
    }
    
    public function inventory(Request $request): JsonResponse
    {
        $query = Stock::query()
            ->with([
                'store',
                'productVariant.product',
            ]);

        if (
            ! $request->user()->hasRole('Super Admin')
        ) {
            $query->where(
                'store_id',
                $request->user()->store_id
            );
        }

        if ($request->filled('store_id')) {
            $query->where(
                'store_id',
                $request->integer('store_id')
            );
        }

        if ($request->boolean('low_stock')) {
            $query
                ->whereColumn(
                    'quantity',
                    '<=',
                    'reorder_level'
                )
                ->where(
                    'reorder_level',
                    '>',
                    0
                );
        }

        if ($request->boolean('out_of_stock')) {
            $query->where(
                'quantity',
                '<=',
                0
            );
        }

        $stocks = $query
            ->orderBy('quantity')
            ->paginate(
                $request->integer('per_page', 50)
            );

        return response()->json([
            'success' => true,
            'message' => 'Inventory report retrieved successfully.',
            'data' => StockResource::collection(
                $stocks->items()
            )->resolve(),
            'meta' => [
                'current_page' => $stocks->currentPage(),
                'last_page' => $stocks->lastPage(),
                'per_page' => $stocks->perPage(),
                'total' => $stocks->total(),
            ],
        ]);
    }

    public function payments(Request $request): JsonResponse
    {
        $query = SalePayment::query()
            ->with([
                'sale.store',
                'createdBy',
            ])
            ->whereHas(
                'sale',
                function ($query) use ($request) {
                    if (
                        ! $request->user()->hasRole(
                            'Super Admin'
                        )
                    ) {
                        $query->where(
                            'store_id',
                            $request->user()->store_id
                        );
                    }

                    if ($request->filled('from')) {
                        $query->whereDate(
                            'sale_date',
                            '>=',
                            $request->input('from')
                        );
                    }

                    if ($request->filled('to')) {
                        $query->whereDate(
                            'sale_date',
                            '<=',
                            $request->input('to')
                        );
                    }
                }
            );

        if ($request->filled('method')) {
            $query->where(
                'method',
                $request->input('method')
            );
        }

        $payments = $query
            ->latest('paid_at')
            ->paginate(
                $request->integer('per_page', 50)
            );

        return response()->json([
            'success' => true,
            'message' => 'Payment report retrieved successfully.',
            'data' => SalePaymentResource::collection(
                $payments->items()
            )->resolve(),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    public function returns(Request $request): JsonResponse
    {
        $query = SaleReturn::query()
            ->with([
                'sale',
                'store',
                'createdBy',
                'items.productVariant.product',
            ]);

        if (
            ! $request->user()->hasRole('Super Admin')
        ) {
            $query->where(
                'store_id',
                $request->user()->store_id
            );
        }

        if ($request->filled('from')) {
            $query->whereDate(
                'return_date',
                '>=',
                $request->input('from')
            );
        }

        if ($request->filled('to')) {
            $query->whereDate(
                'return_date',
                '<=',
                $request->input('to')
            );
        }

        $returns = $query
            ->latest('return_date')
            ->paginate(
                $request->integer('per_page', 50)
            );

        return response()->json([
            'success' => true,
            'message' => 'Returns report retrieved successfully.',
            'data' => SaleReturnResource::collection(
                $returns->items()
            )->resolve(),
            'meta' => [
                'current_page' => $returns->currentPage(),
                'last_page' => $returns->lastPage(),
                'per_page' => $returns->perPage(),
                'total' => $returns->total(),
            ],
        ]);
    }
}
