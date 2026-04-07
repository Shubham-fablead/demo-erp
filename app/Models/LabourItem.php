<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabourItem extends Model
{
    use HasFactory;

    protected $table = 'labour_items';

    protected $fillable = [
        'item_name',
        'price',
        'created_by',
        'isDeleted',
    ];

    protected $casts = [
        'price'     => 'decimal:2',
        'isDeleted' => 'boolean',
    ];

    // Optional scope
    public function scopeActive($query)
    {
        return $query->where('isDeleted', false);
    }
}
