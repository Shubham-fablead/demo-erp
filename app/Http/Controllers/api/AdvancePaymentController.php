<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\AdvancePayment;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AdvancePaymentController extends Controller
{

    public function getFilters(Request $request)
    {
        $user     = Auth::guard('api')->user();
        $userRole = $user->role ?? '';
        $branchId = null;

        $selectedSubAdminId = $request->input('selectedSubAdminId');

        // Role-based branch filtering logic
        if ($userRole === 'admin') {
            if (! empty($selectedSubAdminId)) {
                $branchId = $selectedSubAdminId;
            } else {
                $branchId = $user->id;
            }
        } elseif ($userRole === 'sub-admin') {
            // Sub-admin's own branch
            $branchId = $user->id;
        }

        // Build staff filter query
        $staffQuery = DB::table('users')
        // ->join('users', 'advance_payments.staff_id', '=', 'users.id')
            ->select('users.id', 'users.name')
            ->where('users.isDeleted', 0)
            ->where('users.role', 'staff');

        if ($branchId) {
            $staffQuery->where('users.branch_id', $branchId);
        }

        $staffNames = $staffQuery->distinct()->orderBy('users.name')->get();

        // Get available years from payments
        $yearsQuery = AdvancePayment::selectRaw('YEAR(created_at) as year')
            ->distinct()
            ->orderByDesc('year');

        if ($branchId) {
            $yearsQuery->where('branch_id', $branchId);
        }

        $years = $yearsQuery->pluck('year');

        return response()->json([
            'staff' => $staffNames,
            'years' => $years,
        ]);
    }

    public function index(Request $request)
    {
        $user     = Auth::guard('api')->user();
        $userRole = $user->role;
        $branchId = null;

        // 1. If Admin
        if ($userRole === 'admin') {
            if ($request->filled('selectedSubAdminId')) {
                // Show selected sub-admin's data
                $subAdmin = \App\Models\User::where('id', $request->selectedSubAdminId)
                    ->where('role', 'sub-admin')
                    ->first();

                if ($subAdmin) {
                    $branchId = $subAdmin->id;
                }
            } else {
                // Show admin's own data
                $branchId = $user->id;
            }
        }

        // 2. If Sub-admin → always own data
        if ($userRole === 'sub-admin') {
            $branchId = $user->id;
        }

        // Base query
        $paymentsQuery = AdvancePayment::with('staff:id,name')->where('isDeleted', 0);

        // Filter by branch
        if ($branchId) {
            $paymentsQuery->where('branch_id', $branchId);
        }

        // Search filter (staff name)
        if ($request->filled('search')) {
            $search = $request->search;
            $paymentsQuery->whereHas('staff', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        // Staff filter
        if ($request->filled('staff_id')) {
            $paymentsQuery->where('staff_id', $request->staff_id);
        }

        // Year filter
        if ($request->filled('year')) {
            $paymentsQuery->whereYear('created_at', $request->year);
        }

        // Month filter
        if ($request->filled('month')) {
            $paymentsQuery->whereMonth('created_at', $request->month);
        }

        // Payment mode filter
        if ($request->filled('payment_mode')) {
            $paymentsQuery->where('payment_mode', $request->payment_mode);
        }

        // Date range filter
        if ($request->filled('from_date') && $request->filled('to_date')) {
            $paymentsQuery->whereBetween('payment_date', [
                $request->from_date,
                $request->to_date,
            ]);
        } elseif ($request->filled('from_date')) {
            $paymentsQuery->whereDate('payment_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $paymentsQuery->whereDate('payment_date', '<=', $request->to_date);
        }

        // Calculate totals for the filtered query (before pagination)
        $grandTotalAmount = (float) $paymentsQuery->sum('amount');
        $grandTotalPaid   = (float) $paymentsQuery->sum('paid_amount');
        $grandTotalPending = $grandTotalAmount - $grandTotalPaid;

        // Pagination
        $perPage = $request->input('per_page', 10);
        $page    = $request->input('page', 1);

        $paymentsPaginated = $paymentsQuery->latest()->paginate($perPage, ['*'], 'page', $page);

        $payments = collect($paymentsPaginated->items())->map(function ($payment) {
            return [
                'id'          => $payment->id,
                'staff_id'    => $payment->staff_id,
                'staff_name'  => $payment->staff->name ?? 'N/A',
                'amount'      => $payment->amount,                         // original advance
                'paid_amount' => $payment->paid_amount,                    // already deducted
                'pending'     => $payment->amount - $payment->paid_amount, // 👈 remaining
                'status'      => $payment->status,
                'date'        => $payment->date,
                'method'      => $payment->method,
                'reason'      => $payment->reason,
                'branch_id'   => $payment->branch_id,
            ];
        });

        return response()->json([
            'status' => true,
            'data'   => $payments,
            'pagination' => [
                'current_page'  => $paymentsPaginated->currentPage(),
                'last_page'     => $paymentsPaginated->lastPage(),
                'per_page'      => $paymentsPaginated->perPage(),
                'total'         => $paymentsPaginated->total(),
                'next_page_url' => $paymentsPaginated->nextPageUrl(),
                'prev_page_url' => $paymentsPaginated->previousPageUrl(),
            ],
            'grandTotal'        => number_format($grandTotalAmount, 2, '.', ''),
            'grandTotalPaid'    => number_format($grandTotalPaid, 2, '.', ''),
            'grandTotalPending' => number_format($grandTotalPending, 2, '.', ''),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'staff_id'           => 'required|exists:users,id',
            'amount'             => 'required|numeric',
            'date'               => 'required|date',
            'method'             => 'required|string|max:255',
            'reason'             => 'nullable|string|max:255',
            'selectedSubAdminId' => 'nullable|string',

        ]);

        $user     = Auth::guard('api')->user(); // or simply Auth::user() if you're using web guard
        $userId   = $user->id;
        $userRole = $user->role;
        if ($userRole == 'admin' && (int) $validated['selectedSubAdminId'] > 0) {
            $validated['branch_id'] = (int) $validated['selectedSubAdminId'];
        } else {
            // Fallback to logged-in user's ID
            $validated['branch_id'] = $userId;
        }
        $validated['isDeleted'] = 0;

        $payment = AdvancePayment::create($validated);
         // ==================================================
    // 🔔 CREATE NOTIFICATION (NEW CODE)
    // ==================================================

    $staff = User::find($validated['staff_id']);

    Notification::create([
        'user_id'   => $userId, // creator
        'type'      => 'advance_payment',
        'title'     => 'Advance Payment Added',
        'message'   => "Advance payment of ₹{$payment->amount} added for staff: "
                        . ($staff->name ?? 'N/A'),
        'link'      => '/advance_pay',
        'is_read'   => 0,
        'is_sound'  => 0,
        'branch_id' => $validated['branch_id'],
    ]);


        return response()->json([
            'status'  => "success",
            'message' => 'Advance Payment Details Created',
            'data'    => $payment,
        ]);
    }

    public function show($id)
    {
        $payment = AdvancePayment::with(['staff'])->findOrFail($id);
        return response()->json($payment);
    }

    public function update(Request $request, $id)
    {
        $payment = AdvancePayment::findOrFail($id);

        $validated = $request->validate([
            'staff_id' => 'required|exists:users,id',
            'amount'   => 'required|numeric',
            'date'     => 'required|date',
            'method'   => 'required|string|max:255',
            'reason'   => 'nullable|string|max:255',

        ]);

        $payment->update($validated);

        return response()->json(['status' => true, 'message' => 'Advance Payment Updated', 'data' => $payment]);
    }

    public function destroy($id)
    {
        $payment = AdvancePayment::findOrFail($id);

        // Soft delete by marking as deleted
        $payment->isDeleted = 1;
        $payment->save();

        // Return proper JSON response
        return response()->json([
            'status'  => true,
            'message' => 'Advance Payment Deleted',
            'data'    => [
                'id' => $payment->id,
            ],
        ], 200); // 200 OK
    }

    public function exportAdvancePayments(Request $request)
    {
        $user         = Auth::guard('api')->user();
        $userRole     = $user->role ?? '';
        $userBranchId = $user->id ?? null;

        $selectedSubAdminId = $request->input('selectedSubAdminId');
        $staffId            = $request->input('staff_id');
        $year               = $request->input('year');
        $month              = $request->input('month');

        $query = AdvancePayment::with('staff:id,name,branch_id')->where('isDeleted', 0);

        // Role-based filtering
        if (! empty($selectedSubAdminId)) {
            // Admin exporting selected sub-admin's branch
            $query->whereHas('staff', function ($q) use ($selectedSubAdminId) {
                $q->where('branch_id', $selectedSubAdminId);
            });
        } elseif ($userRole === 'sub-admin' || $userRole === 'admin') {
            // Sub-admin/admin exporting own branch
            $query->whereHas('staff', function ($q) use ($userBranchId) {
                $q->where('branch_id', $userBranchId);
            });
        }
        // else no role-based filtering

        // Apply filters only if they are not empty
        if (! empty($staffId)) {
            $query->where('staff_id', $staffId);
        }
        if (! empty($year)) {
            $query->whereYear('created_at', $year);
        }
        if (! empty($month)) {
            $query->whereMonth('created_at', $month);
        }

        $payments = $query->get();

        // Create spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Headers
        $sheet->setCellValue('A1', 'Staff Name');
        $sheet->setCellValue('B1', 'Amount');
        $sheet->setCellValue('C1', 'Method');
        $sheet->setCellValue('D1', 'Date');
        $sheet->setCellValue('E1', 'Reason');
        $sheet->getStyle('A1:E1')->getFont()->setBold(true);
        $sheet->getStyle('B:B')
      ->getNumberFormat()
      ->setFormatCode('#,##,##0.00');

        // Fill rows
        $row = 2;
        foreach ($payments as $payment) {
            $sheet->setCellValue('A' . $row, $payment->staff->name ?? 'N/A');
            $sheet->setCellValue('B' . $row, $payment->amount);
            $sheet->setCellValue('C' . $row, $payment->method);
            $sheet->setCellValue('D' . $row, $payment->created_at->format('Y-m-d'));
            $sheet->setCellValue('E' . $row, $payment->reason ?? 'N/A');
            $row++;
        }

        // Download file
        $writer   = new Xlsx($spreadsheet);
        $fileName = 'AdvancePayments_' . date('Ymd_His') . '.xlsx';

        $relativePath = 'advancepayment-exports/' . $fileName;

        \Storage::disk('public')->makeDirectory('advancepayment-exports');
        $filePath = storage_path("app/public/{$relativePath}");
        $writer->save($filePath);

        $fileUrl = asset(env('ImagePath') . 'storage/' . $relativePath);

        return response()->json([
            'status'    => true,
            'message'   => 'Advance Payment Excel exported successfully.',
            'file_url'  => $fileUrl,
            'file_name' => $fileName,
        ]);

    }
}
