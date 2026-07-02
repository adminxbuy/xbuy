<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdminSellerMessage extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function sellerProfile()
    {
        return $this->belongsTo(SellerProfile::class, 'seller_profile_id');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
