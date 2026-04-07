<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Labour extends Model
{
    use HasFactory;
    protected  $table = "labour_items";
    protected $fillable = [
        'item_name',
        'price',
        'created_by',
        'isDeleted',
    ];
}
