<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmtpSetting extends Model
{
    use HasFactory;
    protected $table = 'smtp_settings';

    protected $fillable = [
        'mailer', 'host', 'port', 'username', 'password',
        'encryption', 'from_address', 'from_name', 'status', 'branch_id',
    ];

    protected $hidden = ['password'];
    
}
