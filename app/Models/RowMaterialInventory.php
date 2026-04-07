<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RowMaterialInventory extends Model
{
    use HasFactory;
     protected $table = 'row_material_inventory';

    // Mass assignable fields
    protected $fillable = [
        'row_material_id',
        'initial_stock',
        'current_stock',
        'type',
        'branch_id',
        'create_by',
        'date',
    ];

    // Casts
    protected $casts = [
        'date' => 'date',
    ];

    // ✅ Relationships
    public function row_material()
    {
        return $this->belongsTo(RowMaterial::class, 'row_material_id');
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
