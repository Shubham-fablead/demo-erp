<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RowMaterialPurchaseInvoice extends Model
{
    use HasFactory;

    protected $table = 'row_material_purchase_invoice';

    protected $fillable = [
        'branch_id',
        'invoice_number',
        'vendor_id',
        'bill_no',
        'materials',
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
        'materials' => 'array',
    ];

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
