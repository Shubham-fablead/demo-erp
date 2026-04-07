<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConnectedDevice extends Model
{
    protected $table = 'connected_devices';

    protected $fillable = [
        'device_name',
        'device_code',
        'status',
        'session_id',
    ];

    public function scans(): HasMany
    {
        return $this->hasMany(ConnectedDeviceScan::class, 'device_code', 'device_code');
    }
}
