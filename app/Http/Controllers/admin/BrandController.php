<?php

// namespace App\Http\Controllers;
namespace App\Http\Controllers\admin;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller; // Import base Controller class
use App\Models\Brand;

class BrandController extends Controller
{
    public function brand_list(Request $request)
    {
        return view('brand/brandlist');
    }
    public function add_brand(Request $request)
    {
        return view('brand/addbrand');
    }
    public function edit_brand(Request $request, $id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            // Redirect to brand list if brand not found
            return redirect()->route('brand.list')->with('error', 'Brand not found.');
        }

        return view('brand/editbrand', compact('brand', 'id'));
    }
}
