<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BankMaster;

class BankMasterController extends Controller
{
    public function bank_list(Request $request)
    {
        return view('banks/index');
    }
    public function bank_create(Request $request)
    {
        return view('banks.create');
    }
    public function edit_bank(Request $request, $id)
    {
        $bank = BankMaster::findOrFail($id);

        return view('banks/edit', compact('bank'));
    }
    
   
}
