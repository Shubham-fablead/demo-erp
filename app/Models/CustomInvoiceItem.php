<?php
namespace App\Models;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CustomInvoiceItem extends Model
{
    use HasFactory;

    protected $table    = 'custom_invoice_item';
    protected $fillable = [
        'item',
        'quantity',
        'price',
        'product_gst_details',
        'product_gst_total',
        'invoice_status',
        'purchase_status',
        'payment_status',
        'amount_total',
        'vendor_id',
        'customer_id',
        'invoice_id',
        'branch_id',
        'created_by',
        'payment_mode',
        'paid_type',
        'cash_amount',
        'amount',
        'remaining_amount',
        'upi_amount',
        'pending_amount',
        'isDeleted',
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
