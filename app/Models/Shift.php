<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'status',
    ];

    public function batches()
    {
        return $this->hasMany(ProductionBatch::class);
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
