<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    use HasFactory;

    protected $table = 'purchase_return_items';

    protected $fillable = [
        'purchase_return_id',
        'purchase_item_id',
        'product_id',
        'quantity',
        'price',
        'discount',
        'discount_amount',
        'subtotal',
        'product_gst_details',
        'product_gst_total',
        'branch_id',
        'created_by',
        'isDeleted',
    ];

    protected $casts = [
        'product_gst_details' => 'array',
    ];

    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
