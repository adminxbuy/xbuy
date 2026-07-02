<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class SellerProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $appends = ['badge_label', 'badge_color', 'badge_icon', 'badge_icon_url', 'kyc_complete', 'shop_url', 'star_rating', 'total_ratings', 'rating_breakdown'];

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($profile) {
            if ($profile->bank_account_number) {
                $currentAccountNumber = $profile->bank_account_number;
                $allProfiles = self::where('id', '!=', $profile->id)
                    ->whereNotNull('bank_account_number')
                    ->get();
                
                $duplicatesCount = 1;
                $duplicateProfileIds = [];
                foreach ($allProfiles as $otherProfile) {
                    if ($otherProfile->bank_account_number === $currentAccountNumber) {
                        $duplicatesCount++;
                        $duplicateProfileIds[] = $otherProfile->id;
                    }
                }

                if ($duplicatesCount >= 2) {
                    \App\Models\FraudFlag::firstOrCreate(
                        [
                            'flag_type' => 'same_bank_multiple_sellers',
                            'flagged_user_id' => $profile->user_id,
                            'status' => 'pending'
                        ],
                        [
                            'details' => [
                                'seller_profile_id' => $profile->id,
                                'duplicate_profile_ids' => $duplicateProfileIds,
                            ]
                        ]
                    );
                }
            }
        });
    }

    protected function casts(): array
    {
        return [
            'aadhaar_number' => 'encrypted',
            'pan_number' => 'encrypted',
            'bank_account_number' => 'encrypted',
            'bank_ifsc' => 'encrypted',
            'upi_id' => 'encrypted',
            'shop_visit_date' => 'datetime',
            'shop_visit_verified' => 'boolean',
            'kyc_approved_at' => 'datetime',
        ];
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function listings()
    {
        return $this->hasMany(Listing::class, 'seller_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'seller_id');
    }

    public function metrics()
    {
        return $this->hasOne(SellerMetrics::class, 'seller_id');
    }

    public function badgeLogs()
    {
        return $this->hasMany(BadgeLog::class, 'seller_id');
    }

    public function ratings()
    {
        return $this->hasMany(Rating::class, 'seller_id');
    }

    // Accessors
    public function getBadgeLabelAttribute()
    {
        return ucfirst($this->badge_level);
    }

    public function getBadgeColorAttribute()
    {
        return match ($this->badge_level) {
            'fulfilled' => '#713f12', // Gold text
            'verified' => '#1e40af', // Blue text
            'basic' => '#0891b2', // Cyan/blue tick color
            default => '#374151', // Gray text
        };
    }

    public function getBadgeIconAttribute()
    {
        return match ($this->badge_level) {
            'fulfilled' => 'shield-check',
            'verified' => 'check-circle',
            default => 'user',
        };
    }

    public function getBadgeIconUrlAttribute()
    {
        if ($this->badge_level === 'basic') {
            return asset('website_assets/images/basic.png');
        } elseif ($this->badge_level === 'verified') {
            return asset('website_assets/images/verified.png');
        } elseif ($this->badge_level === 'fulfilled') {
            return asset('website_assets/images/FULFILLED.png');
        }
        return null;
    }

    public function getKycCompleteAttribute()
    {
        return $this->kyc_status === 'approved';
    }

    public function getShopUrlAttribute()
    {
        return url('/api/v1/sellers/' . $this->shop_slug);
    }

    public function getStarRatingAttribute()
    {
        return $this->metrics ? (float)$this->metrics->star_rating : 0.0;
    }

    public function getTotalRatingsAttribute()
    {
        return $this->metrics ? (int)$this->metrics->total_ratings : 0;
    }

    public function getRatingBreakdownAttribute()
    {
        $ratings = $this->ratings;
        $breakdown = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
        foreach ($ratings as $rating) {
            $star = (int) round($rating->weighted_total);
            if ($star >= 1 && $star <= 5) {
                $breakdown[$star]++;
            }
        }
        return $breakdown;
    }
}
