<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductInventory extends Model
{
    use HasFactory;

    // Explicit table name (if not pluralized properly by Laravel)
    protected $table = 'product_inventory';

    // Mass assignable fields
    protected $fillable = [
        'product_id',
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
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
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
