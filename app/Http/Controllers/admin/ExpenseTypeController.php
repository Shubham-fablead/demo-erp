<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\ExpenseType;

class ExpenseTypeController extends Controller
{
    public function create_expense_type()
    {
        return view('expense_type/add_expense_type');
    }
    public function expense_type_list()
    {

        return view('expense_type/expense_type_list');
    }
    public function edit_expense_type()
    {
        return view('expense_type/edit_expense_type');
    }
}
