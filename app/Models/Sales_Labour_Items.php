<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

    class Sales_Labour_Items extends Model
{
    use HasFactory;

    protected $table = 'sales_labour_items';
    protected $fillable = [
        'order_id',
        'user_id',
        'labour_item_id',
        'qty',
        'price',
        'created_at',
        'updated_at',
    ];
    
    /**
     * Get the labour item associated with this sales labour item.
     */
    public function labourItem()
    {
        return $this->belongsTo(LabourItem::class, 'labour_item_id');
    }
}
 
