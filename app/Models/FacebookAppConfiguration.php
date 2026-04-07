<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FacebookAppConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'facebook_app_id',
        'facebook_app_secret',
        'phone_number_id',
        'whatsapp_business_account_id',
        'access_token',
        'webhook_url',
        'isDeleted',
        'created_at',
        'updated_at',
    ];

    // Relationship with User (Branch)
    public function branch()
    {
        return $this->belongsTo(User::class, 'branch_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = Carbon::now('Asia/Kolkata');
            $model->updated_at = Carbon::now('Asia/Kolkata');
        });

        static::updating(function ($model) {
            $model->updated_at = Carbon::now('Asia/Kolkata');
        });
    }
}
