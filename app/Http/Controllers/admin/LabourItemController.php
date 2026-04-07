<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LabourItemController extends Controller
{
    public function index()
    {
        return view('labour_item.all_labour_item');
    }
}
