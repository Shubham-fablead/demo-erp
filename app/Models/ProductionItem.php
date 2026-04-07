<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionItem extends Model
{
    use HasFactory;

    protected $table = 'production_items';

    protected $fillable = [
        'production_id',
        'raw_material_id',
        'required_qty',
        'consume_qty',
        'rate',
        'total_cost',
    ];

    public function production()
    {
        return $this->belongsTo(Production::class, 'production_id');
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RowMaterial::class, 'raw_material_id');
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
