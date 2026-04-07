<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppMessageTemplate extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_message_templates';

    protected $fillable = [
        'facebook_app_configuration_id',
        'branch_id',
        'template_id',
        'name',
        'status',
        'on_off',
        'use_for_template',
        'language',
        'category',
        'sub_category',
        'components',
        'isDeleted',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'components' => 'array',
    ];

    // Relationship with FacebookAppConfiguration
    public function facebookAppConfiguration()
    {
        return $this->belongsTo(FacebookAppConfiguration::class, 'facebook_app_configuration_id');
    }

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
