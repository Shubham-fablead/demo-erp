<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'brand_id',
        'branch_id',
        'name',
        'SKU',
        'hsn_code',
        'barcode',
        'description',
        'price', 
        'images',
        'quantity',
        'unit_id',
        'isDeleted',
        'availablility',
        'status',
        'gst_option',
        'product_gst',
        'created_at',
        'updated_at',
        'create_by',
    ];

    protected $appends = ['image_url'];

    // Relationship with Category
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    // Relationship with Brand
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    // protected static function booted()
    // {
    //     static::creating(function ($product) {
    //         do {
    //             $barcode = 'PRD' . mt_rand(1000000000, 9999999999); // 13-char barcode
    //         } while (Product::where('barcode', $barcode)->exists());

    //         $product->barcode = $barcode;
    //     });
    // }
    protected static function booted()
    {
        static::creating(function ($product) {
            if (empty($product->barcode)) {
                do {
                    $barcode = 'PRD' . mt_rand(1000000000, 9999999999);
                } while (Product::where('barcode', $barcode)->exists());

                $product->barcode = $barcode;
            }
        });
    }
    public function getImageUrlAttribute()
    {
        $basePath = env('ImagePath', '/'); // from .env
        $images   = $this->images;

        // If images is JSON (array of images), decode it
        if ($images) {
            $decoded = json_decode($images, true);

            if (is_array($decoded)) {
                // Return full URLs for each image
                return array_map(function ($img) use ($basePath) {
                    return url($basePath . 'storage/' . $img);
                }, $decoded);
            }

            // If it's just a single string
            return url($basePath . 'storage/' . $images);
        }

        // Fallback image
        return [url($basePath . 'admin/assets/img/product/noimage.png')];
    }

    public function product_inventory()
    {
        return $this->hasOne(ProductInventory::class, 'product_id');
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
