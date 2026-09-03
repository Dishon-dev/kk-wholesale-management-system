<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleReturnItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'return_id',
        'product_variant_id',
        'quantity',
        'unit_price',
        'total',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'unit_price' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function saleReturn()
    {
        return $this->belongsTo(
            SaleReturn::class,
            'return_id'
        );
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
