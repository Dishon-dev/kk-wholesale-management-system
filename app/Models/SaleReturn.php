<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleReturn extends Model
{
    protected $table = 'returns';
    
    protected $fillable = [
        'reference',
        'sale_id',
        'store_id',
        'created_by',
        'return_date',
        'subtotal',
        'tax',
        'total',
        'status',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'return_date' => 'datetime',
            'subtotal' => 'decimal:2',
            'tax' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(SaleReturnItem::class, 'return_id');
    }
}
