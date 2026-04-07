<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturnItem extends Model
{
    use HasFactory;

    protected $table = 'sales_return_items';

    protected $fillable = [
        'sales_return_id',
        'order_item_id',
        'product_id',
        'quantity',
        'price',
        'discount',
        'discount_amount',
        'subtotal',
        'product_gst_details',
        'product_gst_total',
    ];

    protected $casts = [
        'product_gst_details' => 'array',
    ];

    public function salesReturn()
    {
        return $this->belongsTo(SalesReturn::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
