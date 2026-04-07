<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'branch_id',
        'month',
        'year',
        'present',
        'absent',
        'extra_present',
        'advance_pay',
        'salary',
        'extra_amount',
        'total_salary',
        'old_advance_pay',
        'status'
    ];
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
    public function getPendingAdvanceAttribute()
    {
        $advance = AdvancePayment::where('staff_id', $this->staff_id)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->first();

        if (!$advance) {
            return 0;
        }

        return $advance->amount - $this->advance_pay;
    }
}
