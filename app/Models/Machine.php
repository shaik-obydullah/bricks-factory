<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Machine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'status',
        'last_maintenance',
        'next_maintenance',
    ];

    protected function casts(): array
    {
        return [
            'last_maintenance' => 'datetime',
            'next_maintenance' => 'datetime',
        ];
    }

    public function batches()
    {
        return $this->hasMany(ProductionBatch::class);
    }
}
