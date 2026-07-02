<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Escrow extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'escrow';
    protected $guarded = [];

    protected $appends = ['is_overdue', 'hours_until_release', 'formatted_release_time'];

    protected function casts(): array
    {
        return [
            'delivery_confirmed_at' => 'datetime',
            'release_scheduled_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Accessors
    public function getIsOverdueAttribute()
    {
        return $this->status === 'held' && $this->release_scheduled_at && $this->release_scheduled_at->isPast();
    }

    public function getHoursUntilReleaseAttribute()
    {
        if (!$this->release_scheduled_at || $this->release_scheduled_at->isPast()) {
            return 0;
        }
        return now()->diffInHours($this->release_scheduled_at);
    }

    public function getFormattedReleaseTimeAttribute()
    {
        return $this->release_scheduled_at ? $this->release_scheduled_at->format('Y-m-d H:i') : null;
    }
}
