<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvancePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'amount',
        'paid_amount',
        'status',
        'date',
        'method',
        'reason',
        'branch_id',
        'isDeleted',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
