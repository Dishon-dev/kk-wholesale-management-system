<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSaleRequest;
use App\Models\Sale;
use App\Models\Store;
use App\Services\SaleService;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(
        protected SaleService $saleService
    ) {}

    public function index(Request $request)
    {
        $query = Sale::query()
            ->with([
                'store',
                'customer',
                'createdBy',
            ]);

        if ($request->filled('store_id')) {
            $query->where(
                'store_id',
                $request->integer('store_id')
            );
        }

        if ($request->filled('customer_id')) {
            $query->where(
                'customer_id',
                $request->integer('customer_id')
            );
        }

        if ($request->filled('payment_status')) {
            $query->where(
                'payment_status',
                $request->string(
                    'payment_status'
                )->toString()
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->string('status')->toString()
            );
        }

        $sales = $query
            ->latest('sale_date')
            ->paginate(
                min(
                    $request->integer('per_page', 20),
                    100
                )
            );

        return response()->json([
            'success' => true,
            'message' =>
                'Sales retrieved successfully.',
            'data' => $sales->items(),
            'meta' => [
                'current_page' =>
                    $sales->currentPage(),
                'last_page' =>
                    $sales->lastPage(),
                'per_page' =>
                    $sales->perPage(),
                'total' =>
                    $sales->total(),
            ],
        ]);
    }

    public function store(
        StoreSaleRequest $request
    ) {
        $store = Store::findOrFail(
            $request->integer('store_id')
        );

        $sale = $this->saleService->create(
            $request->user(),
            $store,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Sale created successfully.',
            'data' => $sale,
        ], 201);
    }

    public function show(Sale $sale)
    {
        $sale->load([
            'store',
            'customer',
            'createdBy',
            'items.productVariant.product',
            'payments.createdBy',
            'returns.items.productVariant.product',
        ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Sale retrieved successfully.',
            'data' => $sale,
        ]);
    }
}
