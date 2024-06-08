<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepartmentCounter extends Model
{
    protected $fillable = [
        'staff_id',
        'department_id',
        'counter_id',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id')->nullable();
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function counter()
    {
        return $this->belongsTo(Counter::class);
    }
}
