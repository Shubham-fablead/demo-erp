<?php

// namespace App\Http\Controllers;
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\PurchaseInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseReturnController extends Controller
{
    public function create_purchase_return(Request $request)
    {
        $user      = Auth::user();
        $branch_id = $user->id;

        $selectedSubAdminId = (session('selectedSubAdminId'));

        // 🔹 Decide branch_id based on role
        if ($user->role === 'staff' && $user->branch_id) {
            $branchIdToUse = $user->branch_id;
        } elseif ($user->role === 'admin' && ! empty($selectedSubAdminId)) {
            $branchIdToUse = $selectedSubAdminId;
        } else {
            $branchIdToUse = $user->id;
        }
        if ($user->role === 'staff') {
            $branchIdToUse = $user->id;

            $invoices = PurchaseInvoice::select('id', 'invoice_number')
                ->where('isDeleted', 0)
                ->where('created_by', $branch_id)
                ->whereHas('vendor', function ($query) {
                    $query->where('isDeleted', 0);
                })
                ->whereHas('purchases', function ($query) {
                    $query->whereRaw('purchases.quantity > (SELECT IFNULL(SUM(quantity), 0) FROM purchase_return_items WHERE purchase_return_items.purchase_item_id = purchases.id)');
                })
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $invoices = PurchaseInvoice::select('id', 'invoice_number')
                ->where('isDeleted', 0)
                ->where('branch_id', $branchIdToUse)
                ->whereHas('vendor', function ($query) {
                    $query->where('isDeleted', 0);
                })
                ->whereHas('purchases', function ($query) {
                    $query->whereRaw('purchases.quantity > (SELECT IFNULL(SUM(quantity), 0) FROM purchase_return_items WHERE purchase_return_items.purchase_item_id = purchases.id)');
                })
                ->orderBy('id', 'desc')
                ->get();
        }
        // dd($invoices);
        $settings         = \DB::table('settings')->where('branch_id', $branchIdToUse)->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        return view('purchasereturn.createpurchasereturn', compact(
            'invoices',
            'currencySymbol',
            'currencyPosition'
        ));
    }

    public function edit_purchase_return(Request $request)
    {
        return view('purchasereturn/editpurchasereturn');
    }
    public function purchase_return_list(Request $request)
    {
        return view('purchasereturn/purchasereturnlist');
    }

}
