<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'user_id',
        'date',
        'check_in_time',
        'check_out_time',
        'work_hours',
        'status',
        'reason',
        'description',
        'extraday'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
