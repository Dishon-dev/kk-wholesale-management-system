<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSalePaymentRequest;
use App\Models\Sale;
use App\Services\SalePaymentService;

class SalePaymentController extends Controller
{
    public function __construct(
        protected SalePaymentService $paymentService
    ) {}

    public function index(Sale $sale)
    {
        $payments = $sale->payments()
            ->with('createdBy')
            ->latest('paid_at')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'message' =>
                'Sale payments retrieved successfully.',
            'data' => $payments->items(),
            'meta' => [
                'current_page' =>
                    $payments->currentPage(),
                'last_page' =>
                    $payments->lastPage(),
                'per_page' =>
                    $payments->perPage(),
                'total' =>
                    $payments->total(),
            ],
        ]);
    }

    public function store(
        StoreSalePaymentRequest $request,
        Sale $sale
    ) {
        $payment = $this->paymentService->create(
            $request->user(),
            $sale,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' =>
                'Payment recorded successfully.',
            'data' => $payment,
        ], 201);
    }
}
