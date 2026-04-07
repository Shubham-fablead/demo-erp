<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /* -------------------------------------------------
     | 🔹 Helpers
     -------------------------------------------------*/

    private function resolveBranchId()
    {
        $user       = auth()->user();
        $role       = strtolower($user->role ?? '');
        $subAdminId = session('selectedSubAdminId');

        return match ($role) {
            'sub-admin' => $user->id,
            'staff'     => $user->branch_id,
            'admin'     => $subAdminId ?: $user->id,
            default     => $user->id,
        };
    }

    private function getBrandsAndCategories($branchId)
    {
        return [
            'brands' => Brand::where('isDeleted', 0)
                ->where('branch_id', $branchId)
                ->get(),

            'categories' => Category::where('isDeleted', 0)
                ->where('branch_id', $branchId)
                ->get(),
        ];
    }

    /* -------------------------------------------------
     | 🔹 Product List
     -------------------------------------------------*/
    public function product_list()
    {
        $branchId = $this->resolveBranchId();
        $data     = $this->getBrandsAndCategories($branchId);

        return view('product/productlist', $data);
    }

    /* -------------------------------------------------
     | 🔹 Add Product
     -------------------------------------------------*/
    public function add_product()
    {
        $branchId = $this->resolveBranchId();
        $data     = $this->getBrandsAndCategories($branchId);
        $units    = Unit::where('is_delete', 0)
            ->where('created_by', $branchId)
            ->get();

        return view('product/addproduct', compact('units') + $data);
    }

    /* -------------------------------------------------
     | 🔹 Edit Product
     -------------------------------------------------*/
    public function edit_product($id)
    {
        $product = Product::with(['category', 'brand'])->findOrFail($id);

        $branchId = $this->resolveBranchId();
        $data     = $this->getBrandsAndCategories($branchId);
        $units = Unit::where('is_delete', 0)
            ->where('created_by', $branchId)
            ->get();
        return view('product/editproduct', array_merge(
            ['product' => $product, 'units' => $units],
            $data
        ));
    }

    /* -------------------------------------------------
     | 🔹 Product Detail (legacy)
     -------------------------------------------------*/
    public function product_detail($id)
    {
        return view('product/product-details', [
            'view_id' => $id,
        ]);
    }

    /* -------------------------------------------------
     | 🔹 Product Import
     -------------------------------------------------*/
    public function product_import()
    {
        return view('product/importproduct');
    }

    /* -------------------------------------------------
     | 🔹 Product View
     -------------------------------------------------*/
    public function product_view($id)
    {
        $product = Product::with(['category', 'brand', 'unit'])->findOrFail($id);

        return view('product/product_view', [
            'product' => $product,
            'view_id' => $id,
        ]);
    }
}
