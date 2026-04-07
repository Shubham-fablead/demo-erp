<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VendorController extends Controller
{
    public function vendor_list(Request $request)
    {
        return view('vendor/supplierlist');
    }
    public function add_vendor(Request $request)
    {
        return view('vendor/addsupplier');
    }
    public function edit_vendor(Request $request)
    {
        return view('vendor/editsupplier');
    }
    public function vendor_report(Request $request)
    {
        return view('vendor/supplierreport');
    }
    public function vendor_view($id)
    {
        return view('vendor.view_vendor', ['id' => $id]);
    }
}
