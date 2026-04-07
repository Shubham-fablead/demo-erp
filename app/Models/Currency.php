<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $table = 'currency'; // Explicitly define the table name

    protected $fillable = [
        'currency_name',
        'currency_symbol',
    ];
}
