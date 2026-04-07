<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AdvancePayment;


class AdvancePaymentController extends Controller
{
    public function index()
    {
        return view('advance_pay.index');
    }

    public function create()
    {
        $user = auth()->user();
        $userRole = $user->role ?? '';
        $userId = $user->id ?? null;
        $subAdminId = session('selectedSubAdminId');
        if ($userRole === 'sub-admin') {
            // Sub-admin sees only their staff
            $staff = User::where('role', 'staff')->where('isDeleted', 0)->where('branch_id', $userId)->get();
        } else if (!empty($subAdminId)) {
            // Admin sees all staff
            $staff = User::where('role', 'staff')->where('isDeleted', 0)->where('branch_id', $subAdminId)->get();
        } elseif ($userRole === 'admin') {
            // Admin sees all staff
            $staff = User::where('role', 'staff')->where('isDeleted', 0)->where('branch_id', $userId)->get();
        }
        // dd($staff);

        return view('advance_pay.create', compact('staff'));
    }
    public function show($id)
    {
        return view('advance_pay.show', compact('id'));
    }

    public function edit($id)
    {
        $payment = AdvancePayment::findOrFail($id);
        $user = auth()->user();
        $userRole = $user->role ?? '';
        $userId = $user->id ?? null;
        $subAdminId = session('selectedSubAdminId');
        if ($userRole === 'sub-admin') {
            // Sub-admin sees only their staff
            $staff = User::where('role', 'staff')->where('isDeleted', 0)->where('branch_id', $userId)->get();
        } else if (!empty($subAdminId)) {
            // Admin sees all staff
            $staff = User::where('role', 'staff')->where('isDeleted', 0)->where('branch_id', $subAdminId)->get();
        } elseif ($userRole === 'admin') {
            // Admin sees all staff
            $staff = User::where('role', 'staff')->where('isDeleted', 0)->where('branch_id', $userId)->get();
        }
        return view('advance_pay.edit', compact('payment', 'staff'));
    }
}
