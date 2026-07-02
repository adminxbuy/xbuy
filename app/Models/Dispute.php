<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dispute extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $appends = ['is_overdue', 'seller_response_deadline_passed', 'hours_since_raised', 'time_since_raised', 'urgency_color', 'urgency_status'];

    protected function casts(): array
    {
        return [
            'evidence_images' => 'json',
            'resolved_at' => 'datetime',
            'seller_response_deadline' => 'datetime',
            'auto_escalated_at' => 'datetime',
        ];
    }

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(SellerProfile::class, 'seller_id');
    }

    public function responses()
    {
        return $this->hasMany(DisputeResponse::class);
    }

    // Accessors
    public function getIsOverdueAttribute()
    {
        return $this->status === 'open' && $this->seller_response_deadline_passed;
    }

    public function getSellerResponseDeadlinePassedAttribute()
    {
        return $this->seller_response_deadline && $this->seller_response_deadline->isPast();
    }

    public function getHoursSinceRaisedAttribute()
    {
        return $this->created_at->diffInHours(now());
    }

    /**
     * Get human-readable time since dispute was raised
     * Example: "5 mins ago", "3 hours ago", "2 days ago"
     */
    public function getTimeSinceRaisedAttribute()
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get urgency status based on hours elapsed
     * Returns: 'critical' (>24hrs), 'high' (12-24hrs), 'normal' (<12hrs)
     */
    public function getUrgencyStatusAttribute()
    {
        $hoursSince = $this->hours_since_raised;
        if ($hoursSince > 24) {
            return 'critical';
        } elseif ($hoursSince >= 12) {
            return 'high';
        }
        return 'normal';
    }

    /**
     * Get CSS color class for urgency
     */
    public function getUrgencyColorAttribute()
    {
        return match($this->urgency_status) {
            'critical' => 'bg-red-50 border-red-200',
            'high' => 'bg-orange-50 border-orange-200',
            default => 'bg-white border-zinc-200',
        };
    }

    /**
     * Get background tint class for table row
     */
    public function getUrgencyRowTintAttribute()
    {
        return match($this->urgency_status) {
            'critical' => 'bg-red-50/40 hover:bg-red-100/40',
            'high' => 'bg-orange-50/40 hover:bg-orange-100/40',
            default => 'hover:bg-zinc-50/50',
        };
    }
}
