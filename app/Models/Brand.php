<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Brand extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($brand) {
            if (empty($brand->slug)) {
                $brand->slug = Str::slug($brand->name);
            }
        });
    }

    public function listings()
    {
        return $this->hasMany(Listing::class, 'brand_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_brand');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
