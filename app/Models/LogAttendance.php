<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogAttendance extends Model
{
    use HasFactory;

    protected $table = 'log_attendance';

    protected $fillable = [
        'user_id',
        'check_date',
        'check_in',
        'checkout_out',
        'branch_id',
        'created_at',
        'updated_at',
    ];

    public $timestamps = true;
}
