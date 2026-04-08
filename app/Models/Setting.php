<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

    class Setting extends Model
    {
        use HasFactory;

        protected $fillable = [
            'branch_id',
            'name',
            'email',
            'phone',
            'state_code',
            'gst_num',
            'low_stock',
            'address',
            'logo',
            'currency_position',
            'currency_symbol',
            'favicon',
            'working_hours',
            'sunday_off',
            'grace_period',
            'lunch_break',
            'open_time',
            'close_time',
            'invoice_size',
            'send_mail',
            'created_at',
            'updated_at',
        ];

    protected $casts = [
        'send_mail' => 'boolean',
    ];

    // Automatically append extra fields
    protected $appends = ['logo_url', 'favicon_url', 'qr_code_url'];

    // ✅ Full logo path
    public function getLogoUrlAttribute()
    {
        if (!empty($this->logo)) {
            return asset(Storage::url($this->logo));
        }
        return asset('admin/assets/img/no-logo.png'); // fallback
    }

    // ✅ Favicon URL
    public function getFaviconUrlAttribute()
    {
        if (!empty($this->favicon)) {
            return asset(Storage::url($this->favicon));
        }
        return asset('admin/assets/img/no-favicon.png'); // fallback
    }

    public function getQrCodeUrlAttribute()
    {
        if (!empty($this->qr_code)) {
            return asset(Storage::url($this->qr_code));
        }
        return asset('admin/assets/img/no-favicon.png'); // fallback
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
