<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;
    protected $table = 'units'; // Specify the table name if it's different from the model name

    protected $fillable = [
        'unit_name',
        'is_delete',
        'created_by'
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'unit_id');
    }
}
