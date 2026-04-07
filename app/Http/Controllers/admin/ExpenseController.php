<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    private function resolveBranchId()
    {
        $user               = Auth::user();
        $selectedSubAdminId = session('selectedSubAdminId');

        if ($user->role === 'staff' && $user->branch_id) {
            return $user->branch_id;
        }

        if ($user->role === 'admin' && ! empty($selectedSubAdminId)) {
            return $selectedSubAdminId;
        }

        return $user->id; // admin / sub-admin default
    }

    public function create_expense()
    {
        $user     = Auth::user();
        $branchId = $this->resolveBranchId();

        $expenseTypes = ExpenseType::where('isDeleted', 0)
            ->when($user->role === 'staff', function ($q) use ($user) {
                $q->where('created_by', $user->id);
            }, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->get();

        return view('expense.createexpense', compact('expenseTypes'));
    }

    public function edit_expense()
    {

        return view('expense/editexpense');
    }
    public function expense_category()
    {
        $branchId = $this->resolveBranchId();

        $expenseTypes = ExpenseType::where('branch_id', $branchId)
            ->where('isDeleted', 0)
            ->get();

        return view('expense.expensecategory', compact('expenseTypes'));
    }

    public function expense_list()
    {
        $branchId = $this->resolveBranchId();

        $years = Expense::where('branch_id', $branchId)
            ->where('isDeleted', 0)
            ->selectRaw('YEAR(expense_date) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return view('expense.expenselist', compact('years'));
    }

    public function expense_report()
    {
        $branchId = $this->resolveBranchId();
        $user     = Auth::user();

        $expenseTypes = ExpenseType::where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->orderBy('type')
            ->get();

        return view('expense.expensereport', compact('expenseTypes'));
    }

    public function expense_report_view($ids)
    {
        $user           = auth()->user();
        $UserID         = $user->id;
        $role           = $user->role;
        $userBranch     = $user->branch_id;
        $selectedBranch = session('selectedSubAdminId');

        // ✅ Decide branch correctly
        if ($role == 'admin' && $selectedBranch) {
            $branchId = $selectedBranch;
        } elseif ($role == 'sub-admin') {
            $branchId = $UserID;
        } elseif ($role == 'staff') {
            $branchId = $userBranch;
        } else {
            $branchId = $UserID;

        }
        $idsArray = explode(',', $ids); // Convert "1,2,3" to [1,2,3]

        $expenses = Expense::whereIn('id', $idsArray)->where('branch_id', $branchId)->get();

        if ($expenses->isEmpty()) {
            return redirect()->route('expense.report')->with('error', 'No expense data found.');
        }

        $settings = Setting::where('branch_id', $branchId)->first();

        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        return view('expense.expense_report', compact('expenses', 'currencySymbol', 'settings', 'currencyPosition', 'ids'));
    }

    public function expense_report_pdf($ids)
    {
        $branchId = $this->resolveBranchId();
        $idsArray = explode(',', $ids);

        $expenses = Expense::whereIn('id', $idsArray)
            ->where('branch_id', $branchId)
            ->get();

        if ($expenses->isEmpty()) {
            return redirect()->route('expense.report')
                ->with('error', 'No expense data found.');
        }

        $setting = Setting::where('branch_id', $branchId)->first();

        $pdf = PDF::loadView('expense.expense-report-pdf', [
            'expenses'         => $expenses,
            'setting'          => $setting,
            'currencySymbol'   => $setting->currency_symbol ?? '₹',
            'currencyPosition' => $setting->currency_position ?? 'left',
        ])->setPaper('A4', 'portrait');

        return $pdf->download('expense_report.pdf');
    }

    public function show_expense_report_page(Request $request)
    {
        $ids      = $request->query('ids');
        $branchId = $request->query('branch');

        if (! $ids || ! $branchId) {
            return abort(400, 'Missing parameters');
        }

        $idsArray = explode(',', $ids);

        $expenses = Expense::whereIn('id', $idsArray)
            ->where('branch_id', $branchId)
            ->get();

        if ($expenses->isEmpty()) {
            return abort(404, 'No expense data found');
        }

        $settings = Setting::where('branch_id', $branchId)->first();

        return view('expense.web_expense_report', [
            'expenses'         => $expenses,
            'settings'         => $settings,
            'currencySymbol'   => $settings->currency_symbol ?? '₹',
            'currencyPosition' => $settings->currency_position ?? 'left',
        ]);
    }

}
