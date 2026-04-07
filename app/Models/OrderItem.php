<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OrderItem extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'user_id',
        'product_id',
        'product_gst_details',
        'product_gst_total',
        'category_id',
        'price',
        'discount_percentage',
        'discount_amount',
        'quantity',
        'total_amount',
        'branch_id',
        'created_by',
        'isDeleted',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'product_gst_details' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }
    // public function product()
    // {
    //     return $this->belongsTo(Product::class);
    // }

    public function invoice()
    {
        return $this->belongsTo(Order::class, 'order_id'); // Assuming invoice means the order
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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
