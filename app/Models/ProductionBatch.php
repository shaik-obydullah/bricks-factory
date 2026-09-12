<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductionBatch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'shift_id',
        'machine_id',
        'quantity_produced',
        'quantity_rejected',
        'start_time',
        'end_time',
        'status',
        'operator_id',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
        ];
    }

    public function order()
    {
        return $this->belongsTo(ProductionOrder::class, 'order_id');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }

    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function qualityChecks()
    {
        return $this->hasMany(QualityCheck::class, 'batch_id');
    }

    public function defects()
    {
        return $this->hasMany(Defect::class, 'batch_id');
    }
}
