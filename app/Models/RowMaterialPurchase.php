<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RowMaterialPurchase extends Model
{
    use HasFactory;

    protected $table = 'row_material_purchases';

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

    public function rowMaterial()
    {
        return $this->belongsTo(RowMaterial::class, 'item', 'id');
    }

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
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
