<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffEarning extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'paid_at'            => 'datetime',
        'min_cap_applied'    => 'boolean',
        'max_cap_applied'    => 'boolean',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Accessors to map order attributes directly for views expecting Order instances
    public function getOrderNumberAttribute()
    {
        return $this->order->order_number ?? '-';
    }

    public function getTotalAmountAttribute()
    {
        return $this->sale_value;
    }

    public function getCommissionPercentAttribute()
    {
        return $this->commission_percentage;
    }

    public function getExpensesAttribute()
    {
        return $this->platform_expenses;
    }

    public function getNetAttribute()
    {
        return $this->final_earning;
    }

    public function getListingAttribute()
    {
        return $this->order->listing ?? null;
    }
}
