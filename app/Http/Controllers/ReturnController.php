<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSaleReturnRequest;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Store;
use App\Services\SaleReturnService;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function __construct(
        protected SaleReturnService $returnService
    ) {}

    public function index(Request $request)
    {
        $query = SaleReturn::query()
            ->with([
                'sale',
                'store',
                'createdBy',
            ]);

        if ($request->filled('store_id')) {
            $query->where(
                'store_id',
                $request->integer('store_id')
            );
        }

        if ($request->filled('sale_id')) {
            $query->where(
                'sale_id',
                $request->integer('sale_id')
            );
        }

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->string('status')->toString()
            );
        }

        $returns = $query
            ->latest('return_date')
            ->paginate(
                min(
                    $request->integer('per_page', 20),
                    100
                )
            );

        return response()->json([
            'success' => true,
            'message' =>
                'Returns retrieved successfully.',
            'data' => $returns->items(),
            'meta' => [
                'current_page' =>
                    $returns->currentPage(),
                'last_page' =>
                    $returns->lastPage(),
                'per_page' =>
                    $returns->perPage(),
                'total' =>
                    $returns->total(),
            ],
        ]);
    }

    public function store(
        StoreSaleReturnRequest $request,
        Sale $sale
    ) {
        $store = Store::findOrFail(
            $request->integer('store_id')
        );

        $return = $this->returnService->create(
            $request->user(),
            $sale,
            $store,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Return processed successfully.',
            'data' => $return,
        ], 201);
    }

    public function show(
        SaleReturn $saleReturn
    ) {
        $saleReturn->load([
            'sale',
            'store',
            'createdBy',
            'items.productVariant.product',
        ]);

        return response()->json([
            'success' => true,
            'message' =>
                'Return retrieved successfully.',
            'data' => $saleReturn,
        ]);
    }
}
