<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    use HasFactory;
    protected  $table="sales_labour_items";
     protected $fillable = [
        'order_id',
        'user_id',
        'labour_item_id',
        'quantity',
        'price',
    ];

    public function labourItem()
{
    return $this->belongsTo(Labour::class, 'labour_item_id');
}
public function product()
{
    return $this->belongsTo(Product::class);
}
}
