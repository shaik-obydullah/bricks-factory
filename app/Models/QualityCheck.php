<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QualityCheck extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'batch_id',
        'check_date',
        'inspector_id',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'check_date' => 'date',
        ];
    }

    public function batch()
    {
        return $this->belongsTo(ProductionBatch::class, 'batch_id');
    }

    public function inspector()
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function items()
    {
        return $this->hasMany(QualityCheckItem::class, 'check_id');
    }
}
