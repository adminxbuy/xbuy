<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoldArchive extends Model
{
    use HasFactory;

    protected $table = 'sold_archive';
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'listing_snapshot' => 'json',
            'sold_at' => 'datetime',
            'dispute_raised' => 'boolean',
        ];
    }

    public function seller()
    {
        return $this->belongsTo(SellerProfile::class, 'seller_id');
    }
}