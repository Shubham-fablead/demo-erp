<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SettingController extends Controller
{
    public function generalsettings(Request $request)
    {
        return view('settings/generalsettings');
    }

    public function facebookappconfiguration(Request $request)
    {
        return view('settings/facebookappconfiguration');
    }
}
