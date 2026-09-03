<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SalePayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalePaymentService
{
    public function create(
        User $user,
        Sale $sale,
        array $data
    ): SalePayment {
        return DB::transaction(function () use (
            $user,
            $sale,
            $data
        ) {
            $sale = Sale::query()
                ->lockForUpdate()
                ->findOrFail($sale->id);

            if ($sale->status !== 'completed') {
                throw new RuntimeException(
                    'Payment cannot be added to this sale.'
                );
            }

            $amount = (float) $data['amount'];

            $paidAmount =
                (float) $sale->paid_amount;

            $total =
                (float) $sale->total;

            $newPaidAmount =
                $paidAmount + $amount;

            $balanceDue =
                max(
                    0,
                    $total - $newPaidAmount
                );

            $changeAmount =
                max(
                    0,
                    $newPaidAmount - $total
                );

            $paymentStatus = match (true) {
                $newPaidAmount <= 0 =>
                    'unpaid',

                $newPaidAmount < $total =>
                    'partial',

                default =>
                    'paid',
            };

            $payment = $sale->payments()->create([
                'amount' => $amount,
                'method' => $data['method'],
                'reference' =>
                    $data['reference'] ?? null,
                'paid_at' =>
                    $data['paid_at'] ?? now(),
                'created_by' => $user->id,
                'notes' =>
                    $data['notes'] ?? null,
            ]);

            $sale->update([
                'paid_amount' => $newPaidAmount,
                'balance_due' => $balanceDue,
                'change_amount' => $changeAmount,
                'payment_status' => $paymentStatus,
            ]);

            return $payment->fresh([
                'sale',
                'createdBy',
            ]);
        });
    }
}
