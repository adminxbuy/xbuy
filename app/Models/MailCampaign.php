<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailCampaign extends Model
{
    protected $guarded = [];

    protected $casts = [
        'sent_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];
}
