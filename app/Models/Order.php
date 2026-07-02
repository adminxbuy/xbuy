<?php

namespace App\Models;

use App\Services\OrderNumberService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        // Auto-generate clean order number before first save
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = OrderNumberService::generate();
            }
        });

        static::updated(function ($order) {
            if ($order->wasChanged('order_status') && $order->order_status === 'completed') {
                $listing = $order->listing;
                if ($listing) {
                    $listing->update([
                        'listing_status' => 'sold',
                        'sold_at' => now(),
                    ]);

                    \App\Models\SoldArchive::firstOrCreate([
                        'listing_id' => $listing->id,
                    ], [
                        'seller_id' => $listing->seller_id,
                        'product_title' => $listing->title,
                        'category' => $listing->category,
                        'grade' => $listing->grade,
                        'serial_number' => $listing->serial_number,
                        'sale_price' => $order->product_amount,
                        'buyer_city' => $order->delivery_address['city'] ?? null,
                        'sold_at' => now(),
                        'listing_snapshot' => $listing->toArray(),
                    ]);
                }

                // Fire staff earnings calculation event
                \App\Events\OrderCompletedEvent::dispatch($order);
            }
        });
    }

    protected $appends = [
        'status_label', 'status_color', 'can_dispute',
        'can_accept', 'can_rate', 'testing_window_remaining_hours'
    ];

    protected function casts(): array
    {
        return [
            'delivery_address' => 'json',
            'warranty_terms_snapshot' => 'json',
            'warranty_accepted_at' => 'datetime',
            'delivered_at' => 'datetime',
            'testing_window_ends_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    // Relationships
    public function listing()
    {
        return $this->belongsTo(Listing::class);
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller()
    {
        return $this->belongsTo(SellerProfile::class, 'seller_id');
    }

    public function staffEarning()
    {
        return $this->hasOne(StaffEarning::class);
    }

    public function escrow()
    {
        return $this->hasOne(Escrow::class);
    }

    public function shipment()
    {
        return $this->hasOne(Shipment::class);
    }

    public function dispute()
    {
        return $this->hasOne(Dispute::class);
    }

    public function rating()
    {
        return $this->hasOne(Rating::class);
    }

    // Accessors
    public function getStatusLabelAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->order_status));
    }

    public function getStatusColorAttribute()
    {
        return match ($this->order_status) {
            'completed' => '#16a34a',
            'disputed', 'cancelled' => '#dc2626',
            default => '#fdd835',
        };
    }

    public function getCanDisputeAttribute()
    {
        return in_array($this->order_status, ['testing_period', 'delivered']);
    }

    public function getCanAcceptAttribute()
    {
        return in_array($this->order_status, ['testing_period', 'delivered']);
    }

    public function getCanRateAttribute()
    {
        return $this->order_status === 'completed' && !$this->rating()->exists();
    }

    public function getTestingWindowRemainingHoursAttribute()
    {
        if (!$this->testing_window_ends_at || $this->testing_window_ends_at->isPast()) {
            return 0;
        }
        return now()->diffInHours($this->testing_window_ends_at);
    }
}
