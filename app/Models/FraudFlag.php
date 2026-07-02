<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FraudFlag extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'flag_type',
        'flagged_user_id',
        'flagged_listing_id',
        'details',
        'status',
        'reviewed_by'
    ];

    protected $casts = [
        'details' => 'array',
    ];

    public function flaggedUser()
    {
        return $this->belongsTo(User::class, 'flagged_user_id');
    }

    public function flaggedListing()
    {
        return $this->belongsTo(Listing::class, 'flagged_listing_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
