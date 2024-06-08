<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'qr_code', 
    ];

    public function departmentCounters()
    {
        return $this->hasMany(DepartmentCounter::class);
    }

}
