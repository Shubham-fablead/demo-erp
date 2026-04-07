<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class InvoiceController extends Controller
{
    public function inventory_report(){

        return view('invoice/inventoryreport');
    }
    public function invoice_report(){
        
        return view('invoice/invoicereport');
    }
   
}
