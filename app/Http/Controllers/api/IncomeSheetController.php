<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use App\Models\PurchaseInvoice;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomeSheetController extends Controller
{

    private function getIncomeSheetData(Request $request)
    {
        $user = Auth::guard('api')->user();

        $branchId     = $user->id;
        $userBranchId = $user->branch_id;
        $userRole     = $user->role;
        // $selectedSubAdminId = session('selectedSubAdminId');
        $selectedSubAdminId = $request->query('selectedSubAdminId') ?? $userBranchId;

        // dd($request->all());
        if ($userRole === 'staff' && $userBranchId) {
            $branchId = $userBranchId;
        } elseif ($userRole === 'admin' && $selectedSubAdminId) {
            $branchId = $selectedSubAdminId;
        }

        $startDate = $request->input('start_date', Carbon::now()->startOfMonth());
        $endDate   = $request->input('end_date', Carbon::now()->endOfMonth());

        $settings = Setting::where('branch_id', $branchId)->first();
        // dd($settings);
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        $ordersQuery = Order::query()
            ->leftJoin('payment_store', 'payment_store.order_id', '=', 'orders.id')
            ->where('orders.branch_id', $branchId)
            ->where('orders.isDeleted', 0)
            ->where('orders.payment_status', 'completed') // ✅ payment_status from orders table
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select('orders.*')
            ->distinct();
        // ->sum('total_amount');
        if ($userRole === 'staff') {
            $ordersQuery->where('orders.created_by', $user->id);
        }

        // 🔹 Total sales amount
        $sales = $ordersQuery->sum('orders.total_amount');

        // 🔹 Sales in Cash
        $salesCash = (clone $ordersQuery)
            ->whereIn('payment_store.payment_method', ['cash', 'Cash'])
            ->sum('payment_store.payment_amount');

        // 🔹 Sales in Online
        $salesOnline = (clone $ordersQuery)
            ->whereIn('payment_store.payment_method', ['online', 'Online', 'debit card', 'Debit Card', 'Debit card', 'scan', 'Scan'])
            ->sum('payment_store.payment_amount');
            // dd($salesOnline);

        // $purchasesQuery = PurchaseInvoice::where('branch_id', $branchId)
        //     ->where('isDeleted', 0)
        //     ->whereBetween('created_at', [$startDate, $endDate]);
        // ->sum('grand_total');

        $purchasesQuery = PurchaseInvoice::query()
            ->join('purchases', 'purchases.invoice_id', '=', 'purchase_invoice.id')
            ->leftJoin('payment_store', 'payment_store.purchase_id', '=', 'purchase_invoice.id')
            ->where('purchase_invoice.branch_id', $branchId)
            ->where('purchases.branch_id', $branchId)
            ->where('purchases.isDeleted', 0)
            // ->where('purchases.payment_status', 'paid') // ✅ payment_status from purchases table
            ->whereBetween('purchase_invoice.created_at', [$startDate, $endDate])
            ->select('purchase_invoice.*')
            ->distinct(); // to avoid duplicate invoices if multiple purchase rows share same invoice_id

        if ($userRole === 'staff') {
            $purchasesQuery->where('purchase_invoice.created_by', $user->id);
        }

        // 🔹 Total purchase amount
        $purchases = $purchasesQuery->sum('purchase_invoice.total_amount');

        // 🔹 Purchases in Cash
        $purchaseCash = (clone $purchasesQuery)
            ->whereIn('payment_store.payment_method', ['cash', 'Cash'])
            ->sum('payment_store.payment_amount');

        // 🔹 Purchases in Online
        $purchaseOnline = (clone $purchasesQuery)
            ->whereIn('payment_store.payment_method', ['online', 'Online', 'debit card', 'Debit Card', 'Debit card', 'scan', 'Scan'])
            ->sum('payment_store.payment_amount');

        $grossProfit = $sales - $purchases;

        $expensesQuery = Expense::where('branch_id', $branchId)
            ->where('isDeleted', 0)
            ->whereBetween('expense_date', [$startDate, $endDate]);
        // ->sum('amount');
        if ($userRole === 'staff') {
            $expensesQuery->where('created_by', $user->id);
        }

        $expenses        = $expensesQuery->sum('amount');
        $operatingIncome = $grossProfit - $expenses;

        return [
            'period'             => [
                'start' => Carbon::parse($startDate)->format('Y-m-d'),
                'end'   => Carbon::parse($endDate)->format('Y-m-d'),
            ],
            'revenue'            => [
                'sales'         => $sales,
                'sales_cash'    => $salesCash,
                'sales_online'  => $salesOnline,
                'total_revenue' => $sales,
            ],
            'cost_of_goods_sold' => [
                'purchases'       => $purchases,
                'purchase_cash'   => $purchaseCash,
                'purchase_online' => $purchaseOnline,
                'total_cogs'      => $purchases,
            ],
            'gross_profit'       => $grossProfit,
            'operating_expenses' => [
                'general_expenses'         => $expenses,
                'total_operating_expenses' => $expenses,
            ],
            'operating_income'   => $operatingIncome,
            'currency'           => [
                'symbol'   => $currencySymbol,
                'position' => $currencyPosition,
            ],
            'settings'           => $settings,
        ];
    }

    public function GetAll(Request $request)
    {
        // dd($request->all());
        $data = $this->getIncomeSheetData($request);
        // dd($data);

        return response()->json([
            'status'  => true,
            'message' => 'Income statement fetched successfully',
            'data'    => $data,
        ], 200);
    }
}
