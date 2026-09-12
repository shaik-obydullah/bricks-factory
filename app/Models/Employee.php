<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'name',
        'phone',
        'email',
        'designation',
        'department',
        'shift_id',
        'joining_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'joining_date' => 'date',
        ];
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function attendance()
    {
        return $this->hasMany(Attendance::class);
    }
}
