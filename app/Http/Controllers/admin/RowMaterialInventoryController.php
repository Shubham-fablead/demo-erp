<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;

class RowMaterialInventoryController extends Controller
{
   public function row_material_inventory(){
       $user = auth()->user();
        $userRole = $user->role ?? '';
        $userId = $user->id ?? null;
        $BranchId = $user->branch_id ?? null;
        $subAdminId = session('selectedSubAdminId');
        if ($userRole === 'sub-admin') {
            $brands = Brand::where('isDeleted', 0)->where('branch_id', $userId)->get();
            $categories = Category::where('isDeleted', 0)->where('branch_id', $userId)->get();
        } elseif ($userRole === 'admin' && $subAdminId) {
            $brands = Brand::where('isDeleted', 0)->where('branch_id', $subAdminId)->get();
            $categories = Category::where('isDeleted', 0)->where('branch_id', $subAdminId)->get();
        } elseif(strtolower($userRole) === 'staff') {
            $brands = Brand::where('isDeleted', 0)->where('branch_id', $BranchId)->get();
            $categories = Category::where('isDeleted', 0)->where('branch_id', $BranchId)->get();
        }else{
            $brands = Brand::where('isDeleted', 0)->where('branch_id', $userId)->get();
            $categories = Category::where('isDeleted', 0)->where('branch_id', $userId)->get();
        }

        return view('row-material_inventory.index', compact('categories', 'brands'));
    }
    public function View(Request $request ,$id)
    {
        return view('row-material_inventory.view');
    }
}
