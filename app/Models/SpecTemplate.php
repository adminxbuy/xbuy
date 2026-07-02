<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecTemplate extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_highlighted' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
