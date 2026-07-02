<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class PageCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::deleting(function ($category) {
            if ($category->isForceDeleting()) {
                $category->pages()->withTrashed()->forceDelete();
            } else {
                $category->pages()->delete();
            }
        });

        static::restoring(function ($category) {
            $category->pages()->onlyTrashed()->restore();
        });
    }

    public function pages()
    {
        return $this->hasMany(Page::class, 'category_id');
    }
}
