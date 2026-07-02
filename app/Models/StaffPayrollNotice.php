<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffPayrollNotice extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'sent_via_email'    => 'boolean',
        'sent_via_whatsapp' => 'boolean',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_user_id');
    }

    public function getTypeAttribute()
    {
        return match($this->notice_type) {
            'salary_slip' => 'Salary Slip',
            'warning' => 'Official Warning',
            'amendment_notice' => 'Amendment Notice',
            'policy_change' => 'Policy Change',
            'general' => 'General Notice',
            'bonus' => 'Bonus / Incentive',
            'suspension_notice' => 'Suspension Notice',
            'termination_notice' => 'Termination Notice',
            default => ucfirst(str_replace('_', ' ', $this->notice_type))
        };
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
