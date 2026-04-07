<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UnitController extends Controller
{
   public function unit_list(Request $request)
    {
        return view('unit/unitlist');
    }
}
