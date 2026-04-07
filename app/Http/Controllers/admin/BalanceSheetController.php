<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BalanceSheetController extends Controller
{

    public function index(Request $request)
    {
        $branchId = session('selectedSubAdminId'); // or Auth::user()->branch_id if single branch

        // ======================
        // 🏷️ Currency Settings
        // ======================
        $settings = DB::table('settings')->first();
        $sym      = $settings->currencySymbol ?? '₹';
        $pos      = $settings->currencyPosition ?? 'left';

        // ======================
        // 🧾 1️⃣ ASSETS
        // ======================

        // Cash (sum of all recorded cash payments)
        
        $cash = DB::table('payment_store')
            ->join('orders', 'orders.id', '=', 'payment_store.order_id')
            ->where('payment_store.isDeleted', 0)
            ->where('payment_store.payment_method', 'cash')
            ->where(function ($q) {
                $q->whereNotNull('payment_store.order_id')
                    ->where('payment_store.order_id', '<>', '')
                    ->where('payment_store.order_id', '>', 0);
            })
            ->where(function ($q) {
                $q->whereNull('payment_store.purchase_id')
                    ->orWhere('payment_store.purchase_id', '=', 0)
                    ->orWhere('payment_store.purchase_id', '=', '');
            })
            ->where(function ($q) {
                $q->whereNull('payment_store.custom_invoice_id')
                    ->orWhere('payment_store.custom_invoice_id', '=', 0)
                    ->orWhere('payment_store.custom_invoice_id', '=', '');
            })
            ->when($branchId, fn($q) => $q->where('orders.branch_id', $branchId))
            ->sum('payment_store.payment_amount');

            // Bank (UPI / Bank / Online)
        $bank = DB::table('payment_store')
            ->join('orders', 'orders.id', '=', 'payment_store.order_id')
            ->where('payment_store.isDeleted', 0)
            ->whereIn('payment_store.payment_method', ['upi', 'bank', 'online','debit card', 'scan'])
            ->where(function ($q) {
                $q->whereNotNull('payment_store.order_id')
                    ->where('payment_store.order_id', '<>', '')
                    ->where('payment_store.order_id', '>', 0);
            })
            ->where(function ($q) {
                $q->whereNull('payment_store.purchase_id')
                    ->orWhere('payment_store.purchase_id', '=', 0)
                    ->orWhere('payment_store.purchase_id', '=', '');
            })
            ->where(function ($q) {
                $q->whereNull('payment_store.custom_invoice_id')
                    ->orWhere('payment_store.custom_invoice_id', '=', 0)
                    ->orWhere('payment_store.custom_invoice_id', '=', '');
            })
            ->when($branchId, fn($q) => $q->where('orders.branch_id', $branchId))
            ->sum('payment_store.payment_amount');

        // Inventory value (sum of product quantity * price)
        // Assuming your `products` table has `quantity` and `price` columns
        $inventory = DB::table('products')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum(DB::raw('quantity * price'));

        // Total Assets
        $totalAssets = $cash + $bank + $inventory;

        $assets = [
            'cash'      => $cash,
            'bank'      => $bank,
            'inventory' => $inventory,
        ];

        // ======================
        // 💸 2️⃣ LIABILITIES
        // ======================

        // Accounts Payable (Unpaid purchase invoices)
        $accountsPayable = DB::table('purchase_invoice')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('remaining_amount');

        // GST Payable (GST from Orders marked as with GST)
        $gstPayable = DB::table('orders')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('gst_option', 1)
            ->sum(DB::raw('(total_amount * 18) / 100')); // change 18 if dynamic tax_id system

        $totalLiabilities = $accountsPayable + $gstPayable;
        // dd($totalLiabilities);

        $liabilities = [
            'accounts_payable' => $accountsPayable,
            'gst_payable'      => $gstPayable,
        ];

        // ======================
        // 📊 3️⃣ EQUITY
        // ======================

        // Net Profit = Total Sales - (Purchases + Expenses)
        $totalSales = DB::table('orders')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        // dd( $totalSales );

        $totalPurchases = DB::table('purchase_invoice')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('grand_total');
        // dd( $totalPurchases );  

        $totalExpenses = DB::table('expenses')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('amount');

        // dd( $totalExpenses );

        // $netProfit = $totalSales - ($totalPurchases + $totalExpenses);


        // Retained Earnings = Total Assets - Total Liabilities + Net Profit
        // $retainedEarnings = ($totalAssets - $totalLiabilities) + $netProfit;
        $retainedEarnings = $totalAssets - $totalLiabilities;

        $equity = [
            'retained_earnings' => $retainedEarnings,
        ];

        // ======================
        // 📘 4️⃣ TOTALS
        // ======================
        $totals = [
            'assets'             => $totalAssets,
            'liabilities'        => $totalLiabilities,
            'liabilities_equity' => $totalLiabilities + $retainedEarnings,
        ];

        // ======================
        // 📄 5️⃣ RETURN VIEW
        // ======================
        return view('accounting.balance-sheet', compact(
            'assets',
            'liabilities',
            'equity',
            'totals',
            'sym',
            'pos'
        ));
    }
}
