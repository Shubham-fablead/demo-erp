<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchases extends Model
{
    use HasFactory;
    protected $fillable = [
        'branch_id',
        'item',
        'quantity',
        'product_gst_details',
        'product_gst_total',
        'price',
        'purchase_status',
        'payment_status',
        'amount_total',
        'discount_amount',
        'discount_percent',
        'vendor_id',
        'invoice_id',
        'isDeleted',
        'created_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'product_gst_details' => 'array',
    ];
    public function product()
    {
        return $this->belongsTo(Product::class, 'item', 'id');
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
    public function invoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'invoice_id'); // Adjust foreign key as per your schema
    }
      public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class, 'purchase_id');
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
