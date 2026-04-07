<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone',
        'address',
        'vehicle_number',
        'vehicle_type',
        'vehicle_brand',
        'vehicle_model',
        'appointment_date',
        'appointment_time',
        'status',
        'remarks',
        'is_deleted',
        'branch_id',
    ];
}
