<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function summary(
        User $user,
        ?int $storeId = null
    ): array {
        $today = Carbon::today();

        $salesQuery = Sale::query();

        $returnsQuery = SaleReturn::query();

        $stockQuery = Stock::query();

        $movementQuery = StockMovement::query();

        if (! $user->hasRole('Super Admin')) {
            $storeId ??= $user->store_id;
        }

        if ($storeId) {
            $salesQuery->where('store_id', $storeId);
            $returnsQuery->where('store_id', $storeId);
            $stockQuery->where('store_id', $storeId);
            $movementQuery->where('store_id', $storeId);
        }

        $todaySales = (clone $salesQuery)
            ->whereDate('sale_date', $today)
            ->where('status', 'completed');

        $todayReturns = (clone $returnsQuery)
            ->whereDate('return_date', $today)
            ->where('status', 'completed');

        $totalInventory = (clone $stockQuery)
            ->sum('quantity');

        $lowStock = (clone $stockQuery)
            ->whereColumn('quantity', '<=', 'reorder_level')
            ->where('reorder_level', '>', 0)
            ->count();

        $outOfStock = (clone $stockQuery)
            ->where('quantity', '<=', 0)
            ->count();

        return [
            'sales' => [
                'today' => [
                    'count' => $todaySales->count(),
                    'amount' => $todaySales->sum('total'),
                ],

                'paid_today' => $todaySales->sum('paid_amount'),

                'outstanding' => (clone $salesQuery)
                    ->where('balance_due', '>', 0)
                    ->where('status', 'completed')
                    ->sum('balance_due'),
            ],

            'returns' => [
                'today' => [
                    'count' => $todayReturns->count(),
                    'amount' => $todayReturns->sum('total'),
                ],
            ],

            'inventory' => [
                'total_quantity' => $totalInventory,
                'low_stock_items' => $lowStock,
                'out_of_stock_items' => $outOfStock,
            ],

            'recent_sales' => Sale::query()
                ->with([
                    'store',
                    'customer',
                    'items.productVariant.product',
                ])
                ->when(
                    $storeId,
                    fn ($query) => $query->where(
                        'store_id',
                        $storeId
                    )
                )
                ->latest('sale_date')
                ->limit(10)
                ->get(),

            'recent_movements' => $movementQuery
                ->with([
                    'store',
                    'productVariant.product',
                    'createdBy',
                ])
                ->latest()
                ->limit(10)
                ->get(),
        ];
    }

    public function salesChart(
        User $user,
        Carbon $from,
        Carbon $to,
        ?int $storeId = null
    ) {
        $query = Sale::query()
            ->selectRaw('DATE(sale_date) as date')
            ->selectRaw('COUNT(*) as transactions')
            ->selectRaw('SUM(total) as total')
            ->selectRaw('SUM(paid_amount) as paid')
            ->whereBetween('sale_date', [
                $from->startOfDay(),
                $to->endOfDay(),
            ])
            ->where('status', 'completed')
            ->groupBy(DB::raw('DATE(sale_date)'))
            ->orderBy('date');

        if (! $user->hasRole('Super Admin')) {
            $storeId ??= $user->store_id;
        }

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        return $query->get();
    }
}
