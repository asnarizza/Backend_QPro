<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerQueue extends Model
{
    protected $fillable = [
        'user_id',
        'counter_id',
        'queue_number',
        'current_queue',
        'joined_at',
        'serviced_at',
        'last_generated_at',
        'last_reset_date',
    ];

    protected $dates = [
        'serviced_at',
        'joined_at',
    ];
}
