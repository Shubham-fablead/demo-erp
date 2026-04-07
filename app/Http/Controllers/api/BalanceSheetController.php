<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BalanceSheetController extends Controller
{
    public function getBalanceSheet(Request $request)
    {

        $user = Auth::guard('api')->user();
        if (! $user) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $branchId = $request->input('selectedSubAdminId', $user->branch_id ?? $user->id);

        // $branchId = session('selectedSubAdminId'); // or Auth::user()->branch_id if single branch

        // ======================
        // 🏷️ Currency Settings
        // ======================
        $settings = DB::table('settings')->first();
        $sym      = $settings->currencySymbol ?? '₹';
        $pos      = $settings->currencyPosition ?? 'left';

        // ======================
        // 🧾 1️⃣ ASSETS
        // ======================
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
            ->whereIn('payment_store.payment_method', ['upi', 'bank', 'online'])
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

        $inventory = DB::table('products')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum(DB::raw('quantity * price'));

        $totalAssets = $cash + $bank + $inventory;

        $assets = [
            'cash'      => $cash,
            'bank'      => $bank,
            'inventory' => $inventory,
            'total'     => $totalAssets,
        ];

        // ======================
        // 💸 2️⃣ LIABILITIES
        // ======================
        $accountsPayable = DB::table('purchase_invoice')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('remaining_amount');

        $gstPayable = DB::table('orders')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->where('gst_option', 1)
            ->sum(DB::raw('(total_amount * 18) / 100')); // change if dynamic

        $totalLiabilities = $accountsPayable + $gstPayable;

        $liabilities = [
            'accounts_payable' => $accountsPayable,
            'gst_payable'      => $gstPayable,
            'total'            => $totalLiabilities,
        ];

        // ======================
        // 📊 3️⃣ EQUITY
        // ======================
        $totalSales = DB::table('orders')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('total_amount');

        $totalPurchases = DB::table('purchase_invoice')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('grand_total');

        $totalExpenses = DB::table('expenses')
            ->where('isDeleted', 0)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->sum('amount');

        $netProfit        = $totalSales - ($totalPurchases + $totalExpenses);
        $retainedEarnings = ($totalAssets - $totalLiabilities) + $netProfit;

        $equity = [
            'net_profit'        => $netProfit,
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
        // 📄 5️⃣ RETURN JSON
        // ======================
        return response()->json([
            'status'   => true,
            'message'  => 'Balance Sheet Data Retrieved Successfully',
            'currency' => [
                'symbol'   => $sym,
                'position' => $pos,
            ],
            'data'     => [
                'assets'      => $assets,
                'liabilities' => $liabilities,
                'equity'      => $equity,
                'totals'      => $totals,
            ],
        ], 200);
    }
}
