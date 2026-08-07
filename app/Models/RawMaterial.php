<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RawMaterial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'unit',
        'current_stock',
        'minimum_stock',
        'status',
    ];

    public function stockMovements()
    {
        return $this->morphMany(StockMovement::class, 'typeable');
    }
}
