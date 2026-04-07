<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseInvoice extends Model
{
    use HasFactory;
    protected $table    = 'purchase_invoice'; // Specify the table name if different from the model name
    protected $fillable = [
        'branch_id',
        'invoice_number',
        'vendor_id',
        'bill_no',
        'products',
        'total_amount',
        'paid',
        'discount',
        'shipping',
        'grand_total',
        'remaining_amount',
        'gst_option',
        'status',
        'taxes',
        'isDeleted',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'products' => 'array', // Automatically convert JSON to array
    ];

    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }
    public function tax()
    {
        return $this->belongsTo(TaxRate::class, 'tax_rate_id');
    }
    public function purchases()
    {
        return $this->hasMany(Purchases::class, 'invoice_id');
    }
    public function payments()
    {
        return $this->hasMany(PaymentStore::class, 'purchase_id')
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
