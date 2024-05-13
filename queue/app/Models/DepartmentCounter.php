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
}
