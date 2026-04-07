<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';
    protected $fillable = [
        'order_number',
        'shipping',
        'user_id',
        'discount',
        'tax_id',
        'gst_option',
        'branch_id',
        'created_by',
        'total_amount',
        'remaining_amount',
        'payment_status',
        'delivery_status',
        'payment_method',
        'order_invoice',
        'quotation_status',
        'approved_status',
        'remarks',
        'isDeleted',
        'created_at',
        'updated_at',
    ];  

    protected $casts = [
        'tax_id' => 'array',
    ];

    public function order_items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }
    public function payments()
    {
        return $this->hasMany(PaymentStore::class, 'order_id', 'id');
    }
    public function returns()
    {
        return $this->hasMany(SalesReturn::class, 'order_id', 'id');
    }
   public function labour_items()
    {
        return $this->hasMany(Sales_Labour_Items::class, 'order_id', 'id');
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
