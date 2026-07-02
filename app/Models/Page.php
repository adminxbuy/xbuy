<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'is_active',
        'is_protected',
        'sort_order',
        'last_edited_by',
        'last_edited_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_protected' => 'boolean',
        'last_edited_at' => 'datetime',
    ];

    public function lastEditedBy()
    {
        return $this->belongsTo(User::class, 'last_edited_by');
    }

    public function category()
    {
        return $this->belongsTo(PageCategory::class, 'category_id');
    }
}
