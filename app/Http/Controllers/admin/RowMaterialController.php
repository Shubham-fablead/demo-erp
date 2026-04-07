<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\RowMaterial;
use App\Models\Unit;
use Illuminate\Http\Request;

class RowMaterialController extends Controller
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
     | 🔹 Row Material List
     -------------------------------------------------*/
    public function row_material_list()
    {
        $branchId = $this->resolveBranchId();
        $data     = $this->getBrandsAndCategories($branchId);

        return view('row_material/rowmaterial_list', $data);
    }

    /* -------------------------------------------------
     | 🔹 Add Row Material
     -------------------------------------------------*/
    public function add_row_material()
    {
        $branchId = $this->resolveBranchId();
        $data     = $this->getBrandsAndCategories($branchId);
        $units    = Unit::where('is_delete', 0)
            ->where('created_by', $branchId)
            ->get();

        return view('row_material/addrowmaterial', compact('units') + $data);
    }

    /* -------------------------------------------------
     | 🔹 Edit
     -------------------------------------------------*/
    public function edit_row_material($id)
    {
        $product = RowMaterial::with(['category', 'brand'])->findOrFail($id);

        $branchId = $this->resolveBranchId();
        $data     = $this->getBrandsAndCategories($branchId);
        $units = Unit::where('is_delete', 0)
            ->where('created_by', $branchId)
            ->get();
        return view('row_material/editrowmaterial', array_merge(
            ['product' => $product, 'units' => $units],
            $data
        ));
    }

    /* -------------------------------------------------
     | 🔹 Product Detail (legacy)
     -------------------------------------------------*/
    public function rowmaterial_detail($id)
    {
        return view('row_material/rowmaterial_view', [
            'view_id' => $id,
        ]);
    }

    /* -------------------------------------------------
     | 🔹 Product Import
     -------------------------------------------------*/

    /* -------------------------------------------------
     | 🔹 Product View
     -------------------------------------------------*/
    // public function product_view($id)
    // {
    //     $product = Product::with(['category', 'brand', 'unit'])->findOrFail($id);

    //     return view('product/product_view', [
    //         'product' => $product,
    //         'view_id' => $id,
    //     ]);
    // }
}
