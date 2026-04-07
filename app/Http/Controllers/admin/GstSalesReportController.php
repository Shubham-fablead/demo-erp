<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GstSalesReportController extends Controller
{
    public function index()
    {
         return view('gst.sales_list');
    }
}
