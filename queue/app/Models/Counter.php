<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Counter extends Model
{
    use HasFactory;

    protected $fillable = ['type', 'staff_id'];

    /**
     * Get the staff member responsible for the counter.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
