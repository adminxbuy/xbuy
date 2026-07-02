<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Subscriber extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'unsubscribed_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscriber) {
            $subscriber->token = Str::random(32);
        });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
