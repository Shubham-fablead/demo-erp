<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Production extends Model
{
    use HasFactory;

    protected $table = 'productions';

    protected $fillable = [
        'production_no',
        'product_id',
        'bom_id',
        'production_qty',
        'output_qty',
        'wastage_qty',
        'wastage_percentage',
        'extra_cost',
        'labor_cost',
        'electricity_cost',
        'total_cost',
        'cost_per_unit',
        'production_date',
        'status',
        'batch_no',
        'expiry_date',
        'notes',
        'branch_id',
        'created_by',
    ];

    protected $casts = [
        'production_date' => 'date:Y-m-d',
        'expiry_date' => 'date:Y-m-d',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function bom()
    {
        return $this->belongsTo(Bom::class, 'bom_id');
    }

    public function items()
    {
        return $this->hasMany(ProductionItem::class, 'production_id');
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
