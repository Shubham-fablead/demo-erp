<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseType;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    public function getExpenses(Request $request)
    {
        $authUser = Auth::guard('api')->user();

        if (! $authUser) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthenticated access',
            ], 401);
        }

        $userId       = $authUser->id;
        $userRole     = $authUser->role;
        $userBranchId = $authUser->branch_id;

        /**
         * ✅ Determine the correct branch ID based on role and context
         */
        if ($userRole === 'staff') {
            // Staff should see data from the branch that created them (their parent branch)
            $branch_id = $authUser->created_by;
        } elseif (! empty($request->selectedSubAdminId)) {
            // Admin viewing a specific sub-admin’s data
            $branch_id = $request->selectedSubAdminId;
        } elseif ($userBranchId) {
            // Sub-admin or user with assigned branch_id
            $branch_id = $userBranchId;
        } else {
            // Admin or fallback to own ID
            $branch_id = $userId;
        }

        // ✅ Build the main expense query
        $query = Expense::with('expenseType')
            ->where('isDeleted', 0);

        // ✅ Role-based filtering
        if ($userRole === 'staff') {
            // Staff only see their own created records
            $query->where('created_by', $userId);
        } else {
            // Admin/sub-admin filter by branch_id
            $query->where('branch_id', $branch_id);
        }

        // ✅ Apply filters
        if ($request->filled('date')) {
            $query->whereDate('expense_date', $request->date);
        }

        if ($request->filled('expense_type_id')) {
            $query->where('expense_type_id', $request->expense_type_id);
        }

        if ($request->filled('month')) {
            $query->whereMonth('expense_date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('expense_date', $request->year);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('expense_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('amount', 'like', "%{$search}%")
                    ->orWhereHas('expenseType', function ($expenseTypeQuery) use ($search) {
                        $expenseTypeQuery->where('type', 'like', "%{$search}%");
                    });

                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $search)) {
                    $q->orWhereDate('expense_date', $search);
                }
            });
        }

        $query->orderBy('id', 'desc');

        $totalAmount = (clone $query)->sum('amount');
        $perPage = (int) $request->input('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;
        $expenses = $query->paginate($perPage);

        // ✅ Fetch branch-specific settings
        $settings = DB::table('settings')
            ->where('branch_id', $branch_id)
            ->first();

        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        return response()->json([
            'status'            => true,
            'currency_symbol'   => $currencySymbol,
            'currency_position' => $currencyPosition,
            'total_amount'      => $totalAmount,
            'data'              => $expenses->items(),
            'pagination'        => [
                'current_page' => $expenses->currentPage(),
                'last_page' => $expenses->lastPage(),
                'per_page' => $expenses->perPage(),
                'total' => $expenses->total(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $authUser = Auth::guard('api')->user();
        $userId   = $authUser->id;
        if ($authUser->role === 'staff') {
            // dd(1);
            $userBranch = $authUser->branch_id;
        } elseif ($authUser->role === 'sub-admin') {
            // dd(2);
            $userBranch = $userId;
        } elseif ($authUser->role == "admin" && ! empty($request->selectedSubAdminId) || $request->selectedSubAdminId != null) {
            // dd(3);
            $userBranch = $request->selectedSubAdminId;
        } else {
            // dd(4);
            $userBranch = $userId;
        }
        // dd($userBranch);
        $validator = Validator::make(
            $request->all(),
            [
                'expense_name'    => 'required|string|max:255',
                'expense_date'    => 'required|date_format:d-m-Y',
                'amount'          => 'required|numeric|min:0',
                'expense_type_id' => 'required|exists:expense_types,id',
                'description'     => 'nullable|string',
            ],
            [], // Custom messages array (optional if not needed)
            [   // Custom attribute names
                'expense_type_id' => 'expense type',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Convert expense_date to Y-m-d for MySQL
        $formattedDate = Carbon::createFromFormat('d-m-Y', $request->expense_date)->format('Y-m-d');

        Expense::create([
            'expense_name'    => $request->expense_name,
            'expense_date'    => $formattedDate,
            'amount'          => $request->amount,
            'expense_type_id' => $request->expense_type_id,
            'description'     => $request->description,
            'branch_id'       => $userBranch ?? $userId,
            'created_by'      => $userId,
        ]);

        return response()->json(['success' => 'Expense added successfully']);
    }

    public function edit($id)
    {
        $authUser = Auth::guard('api')->user();

        if (! $authUser) {
            return response()->json([
                'status'  => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $branch_id          = $authUser->id;
        $selectedSubAdminId = request()->selectedSubAdminId; // optional, for admin context

        // 🔹 Determine correct branch_id based on role
        if ($authUser->role === 'staff' && $authUser->branch_id) {
            $branch_id = $authUser->branch_id;
        } elseif (! empty($selectedSubAdminId)) {
            $branch_id = $selectedSubAdminId;
        } else {
            $branch_id = $authUser->id;
        }

        // 🔹 Fetch expense record
        $expense = Expense::with('expenseType')->findOrFail($id);

        // 🔹 Fetch expense types based on role
        if ($authUser->role === 'staff') {
            // ✅ Staff only gets their created expense types
            $expenseTypes = ExpenseType::where('created_by', $authUser->id)
                ->where('isDeleted', 0)
                ->get();
        } else {
            // ✅ Admin/Sub-admin get expense types of their branch
            $expenseTypes = ExpenseType::where('branch_id', $branch_id)
                ->where('isDeleted', 0)
                ->get();
        }

        return response()->json([
            'status'       => true,
            'data'         => $expense,
            'expenseTypes' => $expenseTypes,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'expense_name'    => 'required|string|max:255',
                'expense_date'    => 'required|date_format:d-m-Y',
                'amount'          => 'required|numeric|min:0',
                'expense_type_id' => 'required|exists:expense_types,id',
                'description'     => 'nullable|string',
            ],
            [], // No custom error messages
            [   // Custom attribute display names
                'expense_type_id' => 'expense type',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $expense = Expense::findOrFail($id);
        $expense->update([
            'expense_name'    => $request->expense_name,
            'expense_date'    => Carbon::createFromFormat('d-m-Y', $request->expense_date)->format('Y-m-d'),
            'amount'          => $request->amount,
            'expense_type_id' => $request->expense_type_id, // update expense_type_id
            'description'     => $request->description,
        ]);

        return response()->json(['success' => 'Expense updated successfully.']);
    }

    public function destroy($id)
    {
        $expense = Expense::find($id);

        if (! $expense) {
            return response()->json(['error' => 'Expense not found.'], 404);
        }

        // Soft delete by setting isDeleted = 1
        $expense->isDeleted = 1;
        $expense->save();

        return response()->json(['success' => 'Expense deleted successfully.']);
    }


    // public function getExpensesReport(Request $request)
    // {
    //     $user         = Auth::guard('api')->user();
    //     $role         = $user->role;
    //     $userId       = $user->id;
    //     $userBranchId = $user->branch_id;

    //     $selectedSubAdminId = $request->query('selectedSubAdminId') ?? $userId;
    //     if ($role == 'sub-admin') {
    //         $selectedSubAdminId = $userId;
    //     } elseif ($role == 'staff') {
    //         // $selectedSubAdminId = $BranchId;
    //         $selectedSubAdminId = $userBranchId ?: $userId;
    //     }
    //     $settings         = DB::table('settings')->where('branch_id', $selectedSubAdminId)->first();
    //     $currencySymbol   = $settings->currency_symbol ?? '₹';
    //     $currencyPosition = $settings->currency_position ?? 'left';

    //     $filter        = $request->query('filter');
    //     $month         = $request->query('month');
    //     $year          = $request->query('year');
    //     $expenseTypeId = $request->query('expense_type_id');
    //     // $expenses = Expense::where('isDeleted', 0)->where('branch_id', $selectedSubAdminId);
    //     $expenses = Expense::where('isDeleted', 0);
    //     if ($role === 'staff') {
    //         // ✅ Staff sees only their own expenses
    //         $expenses->where('created_by', $userId);
    //     } else {
    //         // ✅ Admin/Sub-admin see expenses for their branch
    //         $expenses->where('branch_id', $selectedSubAdminId);
    //     }

    //     if ($filter) {
    //         $today = Carbon::today();
    //         switch ($filter) {
    //             case 'this_week':
    //                 $expenses->whereBetween('expense_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
    //                 break;
    //             case 'this_month':
    //                 $expenses->whereMonth('expense_date', $today->month)
    //                     ->whereYear('expense_date', $today->year);
    //                 break;
    //             case 'last_6_months':
    //                 $expenses->whereBetween('expense_date', [Carbon::now()->subMonths(6), Carbon::now()]);
    //                 break;
    //             case 'this_year':
    //                 $expenses->whereYear('expense_date', $today->year);
    //                 break;
    //             case 'previous_year':
    //                 $expenses->whereYear('expense_date', $today->subYear()->year);
    //                 break;
    //         }
    //     }
    //     // ✅ Manual Month-Year filter (important!)
    //     if (! empty($month)) {
    //         $expenses->whereMonth('expense_date', $month);
    //     }

    //     if (! empty($year)) {
    //         $expenses->whereYear('expense_date', $year);
    //     }

    //     // ✅ Expense Type filter
    //     if (! empty($expenseTypeId)) {
    //         $expenses->where('expense_type_id', $expenseTypeId);
    //     }

    //     $results = $expenses->select('id', 'expense_name', 'amount', 'expense_date', 'description')
    //         ->orderBy('expense_date', 'desc')
    //         ->get()
    //         ->map(function ($expense) use ($currencySymbol, $currencyPosition) {
    //             $formattedAmount           = number_format($expense->amount, 2);
    //             $expense->formatted_amount = $currencyPosition === 'left'
    //                 ? $currencySymbol . ' ' . $formattedAmount
    //                 : $formattedAmount . ' ' . $currencySymbol;
    //             return $expense;
    //         });

    //     return response()->json([
    //         'status' => true,
    //         'data'   => $results,

    //     ]);
    // }
    public function getExpensesReport(Request $request)
    {
        $user = Auth::guard('api')->user();
        $role = $user->role;
        $userId = $user->id;
        $userBranchId = $user->branch_id;

        $selectedSubAdminId = $request->query('selectedSubAdminId') ?? $userId;
        if ($role == 'sub-admin') {
            $selectedSubAdminId = $userId;
        } elseif ($role == 'staff') {
            $selectedSubAdminId = $userBranchId ?: $userId;
        }

        $settings = DB::table('settings')->where('branch_id', $selectedSubAdminId)->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        $filter = $request->query('filter');
        $month = $request->query('month');
        $year = $request->query('year');
        $expenseTypeId = $request->query('expense_type_id');
        $search = $request->query('search', '');

        // Pagination parameters
        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 10);

        $expenses = Expense::where('isDeleted', 0);

        if ($role === 'staff') {
            $expenses->where('created_by', $userId);
        } else {
            $expenses->where('branch_id', $selectedSubAdminId);
        }

        // Apply filters
        if ($filter) {
            $today = Carbon::today();
            switch ($filter) {
                case 'this_week':
                    $expenses->whereBetween('expense_date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    break;
                case 'this_month':
                    $expenses->whereMonth('expense_date', $today->month)->whereYear('expense_date', $today->year);
                    break;
                case 'last_6_months':
                    $expenses->whereBetween('expense_date', [Carbon::now()->subMonths(6), Carbon::now()]);
                    break;
                case 'this_year':
                    $expenses->whereYear('expense_date', $today->year);
                    break;
                case 'previous_year':
                    $expenses->whereYear('expense_date', $today->subYear()->year);
                    break;
            }
        }

        if (!empty($month)) {
            $expenses->whereMonth('expense_date', $month);
        }

        if (!empty($year)) {
            $expenses->whereYear('expense_date', $year);
        }

        if (!empty($expenseTypeId)) {
            $expenses->where('expense_type_id', $expenseTypeId);
        }

        // Apply search filter
        if (!empty($search)) {
            $expenses->where(function ($q) use ($search) {
                $q->where('expense_name', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        // Get total count for pagination
        $totalCount = $expenses->count();

        // Get paginated results
        $results = $expenses->select('id', 'expense_name', 'amount', 'expense_date', 'description')
            ->orderBy('expense_date', 'desc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get()
            ->map(function ($expense) use ($currencySymbol, $currencyPosition) {
                $formattedAmount = number_format($expense->amount, 2);
                $expense->formatted_amount = $currencyPosition === 'left'
                    ? $currencySymbol . ' ' . $formattedAmount
                    : $formattedAmount . ' ' . $currencySymbol;
                return $expense;
            });

        // Get total amount summary for all filtered data (not just current page)
        $totalAmountQuery = clone $expenses;
        $totalAmount = $totalAmountQuery->sum('amount');

        return response()->json([
            'status' => true,
            'data' => $results,
            'currency_symbol' => $currencySymbol,
            'currency_position' => $currencyPosition,
            'summary' => [
                'total_amount' => $totalAmount
            ],
            'pagination' => [
                'current_page' => (int)$page,
                'last_page' => ceil($totalCount / $perPage),
                'per_page' => (int)$perPage,
                'total' => $totalCount,
                'from' => $results->count() > 0 ? (($page - 1) * $perPage) + 1 : 0,
                'to' => $results->count() > 0 ? (($page - 1) * $perPage) + $results->count() : 0,
            ]
        ]);
    }

    public function expense_report_pdf_api(Request $request)
    {
        try {
            $user               = Auth::guard('api')->user();
            $branchId           = $user->id ?? null;
            $UserBranchId       = $user->branch_id ?? null;
            $userRole           = $user->role ?? '';
            $selectedSubAdminId = $request->selectedSubAdminId ?? null;

            // 🔹 Determine branch_id based on role
            if ($userRole === 'sub-admin') {
                $branchId = $branchId;
            } elseif ($userRole === 'admin' && $selectedSubAdminId) {
                $branchId = $selectedSubAdminId;
            } elseif ($userRole === 'staff') {
                $branchId = $UserBranchId;
            }

            // 🔹 Handle IDs input (FormData array OR comma-separated string)
            $ids = $request->input('ids', []);
            if (! is_array($ids)) {
                $ids = explode(',', $ids);
            }
            $ids = array_filter($ids); // remove empty values

            if (empty($ids)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No expense IDs provided.',
                ], 400);
            }

            // 🔹 Fetch expenses
            $expenses = Expense::whereIn('id', $ids)
                ->where('branch_id', $branchId)
                ->get();

            if ($expenses->isEmpty()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No expense data found for the given IDs.',
                ], 404);
            }

            // 🔹 Get settings and currency info
            $setting          = Setting::where('branch_id', $branchId)->first();
            $currencySymbol   = $setting->currency_symbol ?? '₹';
            $currencyPosition = $setting->currency_position ?? 'left';

            // 🔹 Prepare data for PDF
            $pdfData = [
                'expenses'         => $expenses,
                'setting'          => $setting,
                'currencySymbol'   => $currencySymbol,
                'currencyPosition' => $currencyPosition,
            ];

            // 🔹 Generate PDF
            $pdf = PDF::loadView('expense.expense-report-pdf', $pdfData)
                ->setPaper('A4', 'portrait')
                ->setOptions([
                    'defaultFont'          => 'DejaVu Sans',
                    'isHtml5ParserEnabled' => true,
                ]);

            // 🔹 Save file to storage
            $fileName     = 'expense_report_' . now()->format('Ymd_His') . '.pdf';
            $relativePath = 'expenses-reports/' . $fileName;
            \Storage::disk('public')->put($relativePath, $pdf->output());

            // 🔹 Get file URL
            $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

            return response()->json([
                'status'    => true,
                'message'   => 'Expense report PDF generated successfully.',
                'file_url'  => $fileUrl,
                'file_name' => $fileName,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to generate Expense Report PDF.',
                'error'   => $e->getMessage(),
            ]);
        }
    }

    public function view_expense_report(Request $request)
    {
        try {
            $ids                = $request->input('ids');
            $selectedSubAdminId = $request->input('selectedSubAdminId');
            $branchIdToUse      = $request->input('branch');

            if (empty($ids)) {
                return response()->json([
                    'status'  => false,
                    'message' => 'No expense IDs provided.',
                ], 400);
            }

            // Convert array → comma-separated string
            $idsString = is_array($ids) ? implode(',', $ids) : $ids;

            // 🔹 Determine branch ID (with or without auth)
            $authUser = Auth::guard('api')->user();

            if ($authUser) {
                if ($authUser->role === 'staff' && $authUser->branch_id) {
                    $branchIdToUse = $authUser->branch_id;
                } elseif ($authUser->role === 'admin' && ! empty($selectedSubAdminId)) {
                    $branchIdToUse = $selectedSubAdminId;
                } else {
                    $branchIdToUse = $authUser->id;
                }
            } else {
                // No auth → branch required from frontend
                if (! empty($selectedSubAdminId)) {
                    $branchIdToUse = $selectedSubAdminId;
                } elseif (empty($branchIdToUse)) {
                    return response()->json([
                        'status'  => false,
                        'message' => 'Branch ID missing (unauthenticated request).',
                    ], 400);
                }
            }

            // 🔹 Generate URL to web report page
            $reportUrl = url('expense/report/view-page?ids=' . $idsString . '&branch=' . $branchIdToUse);

            return response()->json([
                'status'    => true,
                'message'   => 'Expense report link generated successfully.',
                'view_link' => $reportUrl,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to generate expense report link.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
