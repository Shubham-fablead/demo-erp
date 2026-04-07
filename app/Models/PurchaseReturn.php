<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    use HasFactory;

    protected $table = 'purchase_returns';

    protected $fillable = [
        'purchase_id',
        'return_no',
        'subtotal',
        'discount',
        'discount_amount',
        'tax_amount',
        'shipping',
        'total_amount',
        'refund_amount',
        'branch_id',
        'created_by',
        'isDeleted',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class, 'purchase_return_id');
    }

    public function purchase()
    {
        return $this->belongsTo(Purchases::class);
    }

    // Add this relationship to directly get invoice through purchase
    public function invoice()
    {
        return $this->hasOneThrough(
            PurchaseInvoice::class,
            Purchases::class,
            'id', // Foreign key on purchases table
            'id', // Foreign key on purchase_invoice table
            'purchase_id', // Local key on purchase_returns table
            'invoice_id' // Local key on purchases table
        );
    }
}
