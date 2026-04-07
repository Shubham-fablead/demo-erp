<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\CreditNoteItem;
use App\Models\CreditNotesType;
use App\Models\Order;
use App\Models\PurchaseInvoice;
use Illuminate\Http\Request;

class CreditNoteItemController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $branchId = $user->branch_id ?? $user->id;

        $settings = \DB::table('settings')->where('branch_id', $branchId)->first();
        // dd($user);
        return view('credit-notes-items.index', [
            'currencySymbol'   => $settings->currency_symbol ?? '₹',
            'currencyPosition' => $settings->currency_position ?? 'left',
        ]);
    }


    public function create()
    {
        $user           = auth()->user();
        $UserID         = $user->id;
        $role           = $user->role;
        $userBranch     = $user->branch_id;
        $selectedBranch = session('selectedSubAdminId');

        // Decide branch correctly
        if ($role == 'admin' && $selectedBranch) {
            $branchId = $selectedBranch;
        } elseif ($role == 'sub-admin') {
            $branchId = $UserID;
        } elseif ($role == 'staff') {
            $branchId = $userBranch;
        } else {
            $branchId = $UserID;
        }

        if ($role == 'staff') {
            $invoiceNumbers = Order::where('isDeleted', 0)
                ->where('created_by', $UserID)
                ->orderBy('order_number', 'desc')
                ->pluck('order_number');
            $purchaseInvoiceNumbers = PurchaseInvoice::where('isDeleted', 0)
                ->where('created_by', $UserID)
                ->orderBy('id', 'desc')
                ->pluck('invoice_number');
        } else {
            $invoiceNumbers = Order::where('isDeleted', 0)
                ->where('branch_id', $branchId)
                ->orderBy('order_number', 'desc')
                ->pluck('order_number');
            $purchaseInvoiceNumbers = PurchaseInvoice::where('isDeleted', 0)
                ->where('branch_id', $branchId)
                ->orderBy('id', 'desc')
                ->pluck('invoice_number');
        }

        $creditNoteTypes = CreditNotesType::where('isdeleted', 0)
            ->where('branch_id', $branchId)
            ->get();

        $settings         = \DB::table('settings')->where('branch_id', $branchId)->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        return view('credit-notes-items.create', compact(
            'invoiceNumbers',
            'purchaseInvoiceNumbers',
            'creditNoteTypes',
            'currencySymbol',
            'currencyPosition'
        ));
    }

    // public function show($id)
    // {
    //     $creditNoteItem = CreditNoteItem::with(['order.user.userDetail', 'creditNote'])->findOrFail($id);

    //     $user           = auth()->user();
    //     $UserID         = $user->id;
    //     $role           = $user->role;
    //     $userBranch     = $user->branch_id;
    //     $selectedBranch = session('selectedSubAdminId');

    //     // Decide branch correctly
    //     if ($role == 'admin' && $selectedBranch) {
    //         $branchId = $selectedBranch;
    //     } elseif ($role == 'sub-admin') {
    //         $branchId = $UserID;
    //     } elseif ($role == 'staff') {
    //         $branchId = $userBranch;
    //     } else {
    //         $branchId = $UserID;
    //     }

    //     $settings         = \DB::table('settings')->where('branch_id', $branchId)->first();
    //     $compenyinfo      = $settings;
    //     
    //     $currencyPosition = $settings->currency_position ?? 'left';

    //     return view('credit-notes-items.show', compact(
    //         'creditNoteItem',
    //         'currencySymbol',
    //         'currencyPosition',
    //         'settings',
    //         'compenyinfo'
    //     ));
    // }
    public function show($id)
    {
        $user = auth()->user();
        $branchId = $user->branch_id ?? $user->id;

        $settings = \DB::table('settings')->where('branch_id', $branchId)->first();

        return view('credit-notes-items.show', [
            'id' => $id,
            'currencySymbol'   => $settings->currency_symbol ?? '₹',
            'currencyPosition' => $settings->currency_position ?? 'left',
            'compenyinfo' => $settings
        ]);
    }



    public function edit($id)
    {
        $creditNoteItem = CreditNoteItem::findOrFail($id);

        $user           = auth()->user();
        $UserID         = $user->id;
        $role           = $user->role;
        $userBranch     = $user->branch_id;
        $selectedBranch = session('selectedSubAdminId');

        // Decide branch correctly
        if ($role == 'admin' && $selectedBranch) {
            $branchId = $selectedBranch;
        } elseif ($role == 'sub-admin') {
            $branchId = $UserID;
        } elseif ($role == 'staff') {
            $branchId = $userBranch;
        } else {
            $branchId = $UserID;
        }

        if ($role == 'staff') {
            $invoiceNumbers = Order::where('isDeleted', 0)
                ->where('created_by', $UserID)
                ->orderBy('order_number', 'desc')
                ->pluck('order_number');
            $purchaseInvoiceNumbers = PurchaseInvoice::where('isDeleted', 0)
                ->where('created_by', $UserID)
                ->orderBy('id', 'desc')
                ->pluck('invoice_number');
        } else {
            $invoiceNumbers = Order::where('isDeleted', 0)
                ->where('branch_id', $branchId)
                ->orderBy('order_number', 'desc')
                ->pluck('order_number');
            $purchaseInvoiceNumbers = PurchaseInvoice::where('isDeleted', 0)
                ->where('branch_id', $branchId)
                ->orderBy('id', 'desc')
                ->pluck('invoice_number');
        }

        $creditNoteTypes = CreditNotesType::where('isdeleted', 0)
            ->where('branch_id', $branchId)
            ->get();

        $settings         = \DB::table('settings')->where('branch_id', $branchId)->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        return view('credit-notes-items.edit', compact(
            'creditNoteItem',
            'invoiceNumbers',
            'purchaseInvoiceNumbers',
            'creditNoteTypes',
            'currencySymbol',
            'currencyPosition'
        ));
    }
}
