<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    use HasFactory;

    protected $table = 'sales_returns';

    protected $fillable = [
        'order_id',
        'return_number',
        'subtotal',
        'discount',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'refund_amount',
        'branch_id',
        'created_by',
    ];

    public function items()
    {
        return $this->hasMany(SalesReturnItem::class, 'sales_return_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
