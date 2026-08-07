<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'status',
    ];

    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            if ($category->isDirty('name') || ! $category->slug) {
                $category->slug = $category->uniqueSlug($category->name);
            }
        });
    }

    protected function uniqueSlug(string $name): string
    {
        $base = \Illuminate\Support\Str::slug($name) ?: 'category';
        $slug = $base;
        $i = 2;
        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $base . '-' . $i++;
        }
        return $slug;
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
