<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\BankMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class transactionController extends Controller
{
    private function resolveBranchId()
    {
        $user               = Auth::user();
        $selectedSubAdminId = session('selectedSubAdminId');

        if ($user->role === 'staff' && $user->branch_id) {
            return $user->branch_id;
        }

        if ($user->role === 'admin' && !empty($selectedSubAdminId)) {
            return $selectedSubAdminId;
        }

        return $user->id; // admin / sub-admin default
    }

    /** BANK BOOK */
    public function bankBook()
    {
        $branchId = $this->resolveBranchId();

        $banks = BankMaster::where('branch_id', $branchId)
            ->where('isDeleted', 0)
            ->get();

        return view('transaction.bankbook', compact('banks'));
    }

    /** CASH BOOK */
    public function cashBook()
    {
        $branchId = $this->resolveBranchId();

        $banks = BankMaster::where('branch_id', $branchId)
            ->where('isDeleted', 0)
            ->get();

        return view('transaction.cashbook', compact('branchId', 'banks'));
    }
}
