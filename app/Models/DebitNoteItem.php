<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebitNoteItem extends Model
{
    use HasFactory;
     protected $table = 'debit_notes_type';
    protected $fillable = [
        'user_id',
        'branch_id',
        'transaction_type',
        'order_id',
        'purchase_id',
        'create_note_id',
        'invoice_number',
        'grand_total',
        'remaning_amount',
        'total_paid',
        'settlement_amount',
        'reason',
        'total',
        'isDeleted'
    ];

    public function creditNoteType()
    {
        return $this->belongsTo(CreditNotesType::class, 'create_note_id');
    }

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}
