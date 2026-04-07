<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditNotesType extends Model
{
    use HasFactory;
     protected $table = 'credit_notes_type';
     protected $fillable = [
        'type_name',
        'branch_id',
        'isdeleted',
    ];
}
