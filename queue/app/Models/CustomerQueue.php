<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerQueue extends Model
{

    // Define status constants
    const STATUS_WAITING = 0;
    const STATUS_SERVICED = 1;
    const STATUS_ON_HOLD = 2;

    protected $fillable = [
        'user_id',
        'department_id',
        'counter_id',
        'queue_number',
        'current_queue',
        'next_queue',
        'joined_at',
        'serviced_at',
        'status',
        'last_reset_date',
    ];

    protected $dates = [
        'serviced_at',
        'joined_at',
    ];
}
