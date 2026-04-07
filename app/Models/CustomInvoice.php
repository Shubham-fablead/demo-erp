<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CustomInvoice extends Model
{
    use HasFactory;
    protected $table = 'custom_invoice'; // Specify the table name if different from the model name
    protected $fillable = [
        'invoice_number',
        'branch_id',
        'vendor_id',
        'customer_id',
        'products',
        'total_amount',
        'paid',
        'discount',
        'shipping',
        'grand_total',
        'status',
        'taxes',
        'remaining_amount',
        'gst_option',
        'isDeleted',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'products' => 'array', // Automatically convert JSON to array
        'taxes'    => 'array',
    ];

      // ✅ Vendor Relationship (only users with role = vendor)
    public function vendor()
    {
        return $this->belongsTo(User::class, 'vendor_id')
                    ->where('role', 'vendor');
    }

    // ✅ Customer Relationship (only users with role = customer)
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id')
                    ->where('role', 'customer');
    }

    public function payments()
    {
        return $this->hasMany(PaymentStore::class, 'custom_invoice_id');
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
