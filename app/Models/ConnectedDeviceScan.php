<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConnectedDeviceScan extends Model
{
    protected $table = 'connected_device_scans';

    protected $fillable = [
        'device_code',
        'barcode',
        'consumed_at',
    ];

    protected $casts = [
        'consumed_at' => 'datetime',
    ];
}

