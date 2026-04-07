<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Category extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'image',
        'branch_id',
        'isDeleted',
        'created_at',
        'updated_at',
    ];

    protected $appends = ['image_url'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getImageUrlAttribute()
    {
        $basePath = env('ImagePath', '/'); // default "/" if not set

        if ($this->image) {
            return url($basePath . 'storage/' . $this->image);
        }

        // fallback category image
        return url($basePath . 'admin/assets/img/product/noimage.png');
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
