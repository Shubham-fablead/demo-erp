<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use App\Models\PurchaseInvoice;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class IncomeSheetController extends Controller
{
    // Extract data preparation to a private method
    private function getIncomeSheetData(Request $request)
    {
        $user               = Auth::user();
        $branchId           = $user->id;
        $userBranchId       = $user->branch_id;
        $userRole           = $user->role;
        $selectedSubAdminId = session('selectedSubAdminId');
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



        // 🔹 Sales in Cash
        $salesCash = (clone $ordersQuery)
            ->whereIn('payment_store.payment_method', ['cash', 'Cash'])
            ->sum('payment_store.payment_amount');

        // 🔹 Sales in Online
        $salesOnline = (clone $ordersQuery)
            ->whereIn('payment_store.payment_method', ['online', 'Online', 'debit card', 'Debit Card', 'Debit card', 'scan', 'Scan'])
            ->sum('payment_store.payment_amount');
        // 🔹 Total sales amount
        $sales = $salesCash + $salesOnline;
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

        // 🔹 Purchases in Cash
        $purchaseCash = (clone $purchasesQuery)
            ->whereIn('payment_store.payment_method', ['cash', 'Cash'])
            ->sum('payment_store.payment_amount');
        // dd($purchaseCash);
        // 🔹 Purchases in Online
        $purchaseOnline = (clone $purchasesQuery)
            ->whereIn('payment_store.payment_method', ['online', 'Online', 'debit card', 'Debit Card', 'Debit card', 'scan', 'Scan'])
            ->sum('payment_store.payment_amount');
        $purchases = $purchaseCash + $purchaseOnline;

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

    public function index(Request $request)
    {

        $data = $this->getIncomeSheetData($request);
        return view('income-statement.index', compact('data'));
    }

    public function generatePdf(Request $request)
    {
        // dd('asd');
        $data = $this->getIncomeSheetData($request);
        $pdf  = PDF::loadView('income-statement.pdf', compact('data'));
        return $pdf->download('income-statement.pdf');
    }

    public function generateExcel(Request $request)
    {
        $data     = $this->getIncomeSheetData($request);
        $currency = $data['currency']['symbol'] ?? '₹';

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Income Statement');

        $row = 1;

        // Header
        $sheet->setCellValue("A$row", 'Income Statement');
        $sheet->mergeCells("A$row:B$row");
        $sheet->getStyle("A$row")->getFont()->setBold(true)->setSize(14);
        $row++;

        // Period
        $sheet->setCellValue("A$row", 'Period:');
        $sheet->setCellValue("B$row", $data['period']['start'] . ' to ' . $data['period']['end']);
        $row += 2;

        // Revenue Section
        $sheet->setCellValue("A$row", 'Revenue');
        $sheet->mergeCells("A$row:B$row");
        $sheet->getStyle("A$row")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCE5FF');
        $sheet->getStyle("A$row")->getFont()->setBold(true);
        $row++;

        // 🔹 Sales in Cash
        $sheet->setCellValue("A$row", 'Sales in Cash');
        $sheet->setCellValue("B$row", $currency . number_format($data['revenue']['sales_cash'], 2));
        $row++;

        // 🔹 Sales in Online
        $sheet->setCellValue("A$row", 'Sales in Online');
        $sheet->setCellValue("B$row", $currency . number_format($data['revenue']['sales_online'], 2));
        $row++;

        // 🔹 Sales Revenue (Total)
        $sheet->setCellValue("A$row", 'Sales Revenue');
        $sheet->setCellValue("B$row", $currency . number_format($data['revenue']['sales'], 2));
        $row++;

        // 🔹 Total Revenue
        $sheet->setCellValue("A$row", 'Total Revenue');
        $sheet->setCellValue("B$row", $currency . number_format($data['revenue']['total_revenue'], 2));
        $sheet->getStyle("A$row:B$row")->getFont()->setBold(true);
        $row += 2;

        // Cost of Goods Sold
        $sheet->setCellValue("A$row", 'Cost of Goods Sold');
        $sheet->mergeCells("A$row:B$row");
        $sheet->getStyle("A$row")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCE5FF');
        $sheet->getStyle("A$row")->getFont()->setBold(true);
        $row++;

        // 🔹 Purchases in Cash
        $sheet->setCellValue("A$row", 'Purchases in Cash');
        $sheet->setCellValue("B$row", $currency . number_format($data['cost_of_goods_sold']['purchase_cash'], 2));
        $row++;

        // 🔹 Purchases in Online
        $sheet->setCellValue("A$row", 'Purchases in Online');
        $sheet->setCellValue("B$row", $currency . number_format($data['cost_of_goods_sold']['purchase_online'], 2));
        $row++;

        // 🔹 Purchases Total
        $sheet->setCellValue("A$row", 'Purchases');
        $sheet->setCellValue("B$row", $currency . number_format($data['cost_of_goods_sold']['purchases'], 2));
        $row++;

        // 🔹 Total COGS
        $sheet->setCellValue("A$row", 'Total Cost of Goods Sold');
        $sheet->setCellValue("B$row", $currency . number_format($data['cost_of_goods_sold']['total_cogs'], 2));
        $sheet->getStyle("A$row:B$row")->getFont()->setBold(true);
        $row += 2;

        // Gross Profit
        $sheet->setCellValue("A$row", 'Gross Profit');
        $sheet->setCellValue("B$row", $currency . number_format($data['gross_profit'], 2));
        $sheet->getStyle("A$row:B$row")->getFont()->setBold(true);
        $sheet->getStyle("A$row:B$row")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCFFCC');
        $row += 2;

        // Operating Expenses
        $sheet->setCellValue("A$row", 'Operating Expenses');
        $sheet->mergeCells("A$row:B$row");
        $sheet->getStyle("A$row")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCE5FF');
        $sheet->getStyle("A$row")->getFont()->setBold(true);
        $row++;

        $sheet->setCellValue("A$row", 'General Expenses');
        $sheet->setCellValue("B$row", $currency . number_format($data['operating_expenses']['general_expenses'], 2));
        $row++;

        $sheet->setCellValue("A$row", 'Total Operating Expenses');
        $sheet->setCellValue("B$row", $currency . number_format($data['operating_expenses']['total_operating_expenses'], 2));
        $sheet->getStyle("A$row:B$row")->getFont()->setBold(true);
        $row += 2;

        // Operating Income
        $sheet->setCellValue("A$row", 'Operating Income (EBIT)');
        $sheet->setCellValue("B$row", $currency . number_format($data['operating_income'], 2));
        $sheet->getStyle("A$row:B$row")->getFont()->setBold(true);
        $sheet->getStyle("A$row:B$row")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFCCE5FF');

        // Column widths
        $sheet->getColumnDimension('A')->setWidth(35);
        $sheet->getColumnDimension('B')->setWidth(20);

        // Borders
        $sheet->getStyle("A1:B$row")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Output as download
        $writer   = new Xlsx($spreadsheet);
        $fileName = 'Income-Statement.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        $writer->save('php://output');
        exit;
    }
}
