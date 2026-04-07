<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class CreditNoteItem extends Model
{
    use HasFactory;

    protected $table = 'credit_note_items';
    public $timestamps = false;

    // Fillable fields for mass assignment
    protected $fillable = [
        'credite_note_id',
        'type_id',
        'order_id',
        'purchase_id',
        'user_id',
        'branch_id',
        'total_amt',
        'remaining_amt',
        'total_paid',
        'settlement_amount',
        'reason',
        'total',
        'isDeleted',
        'created_at',
        'updated_at',
    ];


    public function creditNote()
    {
        return $this->belongsTo(CreditNotesType::class, 'credite_note_id');
    }

    /**
     * Relationship: CreditNoteItem belongs to an Order
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
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
