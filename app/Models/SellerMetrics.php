<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerMetrics extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'last_calculated_at' => 'datetime',
        ];
    }

    public function seller()
    {
        return $this->belongsTo(SellerProfile::class, 'seller_id');
    }
}