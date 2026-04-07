<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CustomerController extends Controller
{
    public function customer_list(Request $request)
    {
        return view('customer/customerlist');
    }
    public function add_customer(Request $request)
    {
        return view('customer/addcustomer');
    }
    public function edit_customer(Request $request)
    {
        return view('customer/editcustomer');
    }
    public function customer_report(Request $request)
    {
        return view('customer/customerreport');
    }
    public function customer_view($id)
    {
        return view('customer.view_customer', ['id' => $id]);
    }
}
