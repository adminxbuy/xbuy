<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'pickup_scheduled_at' => 'datetime',
            'picked_up_at' => 'datetime',
            'delivered_at' => 'datetime',
            'estimated_delivery_date' => 'datetime',
            'tracking_events' => 'json',
            'is_reverse_pickup' => 'boolean',
        ];
    }

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function parentShipment()
    {
        return $this->belongsTo(Shipment::class, 'parent_shipment_id');
    }

    public function returnShipments()
    {
        return $this->hasMany(Shipment::class, 'parent_shipment_id');
    }
}
