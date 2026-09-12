<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionTarget extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'target_date',
        'target_quantity',
        'actual_quantity',
    ];

    protected function casts(): array
    {
        return [
            'target_date' => 'date',
        ];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
