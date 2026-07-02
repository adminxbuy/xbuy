<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffProfile extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'appointment_date'  => 'date',
        'termination_date'  => 'date',
        'bank_account'      => 'encrypted',
        'upi_id'            => 'encrypted',
        'pan_number'        => 'encrypted',
        'aadhaar_number'    => 'encrypted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
