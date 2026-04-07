<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\CreditNotesType;
use Illuminate\Http\Request;

class CreditNotesTypeController extends Controller
{
    //
    public function create()
    {
        return view('credit_notes.create');
    }
    public function creditnotes_list(Request $request)
    {
        return view('credit-notes.index');
    }
    public function edit($id)
    {
        $creditNote = CreditNotesType::findOrFail($id);
        return view('credit_notes.edit', compact('creditNote'));
    }
}
