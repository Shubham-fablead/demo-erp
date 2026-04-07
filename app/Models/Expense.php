<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = ['branch_id','expense_name', 'expense_date', 'amount', 'description', 'expense_type_id', 'isDeleted','created_by'];

    public function expenseType()
{
    return $this->belongsTo(ExpenseType::class, 'expense_type_id')
        ->where('isDeleted', 0);
}

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = Carbon::now('Asia/Kolkata');
            $model->updated_at = Carbon::now('Asia/Kolkata');
        });

        static::updating(function ($model) {
            $model->updated_at = Carbon::now('Asia/Kolkata');
        });
    }
}
