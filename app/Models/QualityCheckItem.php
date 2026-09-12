<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualityCheckItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'check_id',
        'parameter',
        'expected_value',
        'actual_value',
        'status',
    ];

    public function check()
    {
        return $this->belongsTo(QualityCheck::class, 'check_id');
    }
}
