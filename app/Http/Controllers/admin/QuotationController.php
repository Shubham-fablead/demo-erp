<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class QuotationController extends Controller
{
    public function quotation_list(Request $request)
    {
        return view('quotation/quotationList');
    }
    public function add_quotation(Request $request)
    {
        return view('quotation/addquotation');
    }
    public function edit_quotation(Request $request)
    {
        return view('quotation/editquotation');
    }
}
