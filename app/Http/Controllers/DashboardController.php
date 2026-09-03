<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        protected DashboardService $dashboardService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->dashboardService->summary(
            $request->user(),
            $request->integer('store_id') ?: null
        );

        return response()->json([
            'success' => true,
            'message' => 'Dashboard retrieved successfully.',
            'data' => $data,
        ]);
    }

    public function salesChart(Request $request): JsonResponse
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))
            : now()->subDays(30);

        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))
            : now();

        $data = $this->dashboardService->salesChart(
            $request->user(),
            $from,
            $to,
            $request->integer('store_id') ?: null
        );

        return response()->json([
            'success' => true,
            'message' => 'Sales chart retrieved successfully.',
            'data' => $data,
        ]);
    }
}
