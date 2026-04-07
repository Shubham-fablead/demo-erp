<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseTypeController extends Controller
{
    /* -------------------------------------------------
     | 🔹 Helper: Resolve Branch ID
     -------------------------------------------------*/
    private function resolveBranchId(Request $request)
    {
        $user = Auth::guard('api')->user();

        return match ($user->role) {
            'staff'     => $user->branch_id,
            'sub-admin' => $user->id,
            'admin'     => $request->selectedSubAdminId ?: $user->id,
            default     => $user->id,
        };
    }

    /* -------------------------------------------------
     | 🔹 Create Expense Type
     -------------------------------------------------*/
    public function store(Request $request)
    {
        $validated = $request->validate(
            [
                'type' => 'required|string|max:255|unique:expense_types,type',
            ],
            [],
            ['type' => 'expense type']
        );

        $user = Auth::guard('api')->user();

        ExpenseType::create([
            'type'       => $validated['type'],
            'branch_id'  => $this->resolveBranchId($request),
            'created_by' => $user->id,
            'isDeleted'  => 0,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Expense type added successfully.',
        ]);
    }

    /* -------------------------------------------------
     | 🔹 List Expense Types
     -------------------------------------------------*/
    // public function list(Request $request)
    // {
    //     $user = Auth::guard('api')->user();

    //     if (! $user) {
    //         return response()->json([
    //             'status'  => false,
    //             'message' => 'Unauthorized',
    //         ], 401);
    //     }

    //     $query = ExpenseType::where('isDeleted', 0);

    //     if ($user->role === 'staff') {
    //         $query->where('created_by', $user->id);
    //     } else {
    //         $query->where('branch_id', $this->resolveBranchId($request));
    //     }

    //     return response()->json([
    //         'status' => true,
    //         'data'   => $query->latest('id')->get(),
    //     ]);
    // }
   public function list(Request $request)
{
    $user = Auth::guard('api')->user();

    if (! $user) {
        return response()->json([
            'status'  => false,
            'message' => 'Unauthorized',
        ], 401);
    }

    $perPage = $request->get('per_page', 10);
    $search  = $request->get('search', '');

    $query = ExpenseType::where('isDeleted', 0);

    // Apply branch / created_by filter
    if ($user->role === 'staff') {
        $query->where('created_by', $user->id);
    } else {
        $query->where('branch_id', $this->resolveBranchId($request));
    }

    // Apply search on 'type' field
    if (!empty($search)) {
        $query->where('type', 'LIKE', "%{$search}%");
    }

    $expenseTypes = $query->latest('id')->paginate($perPage);

    return response()->json([
        'status'     => true,
        'data'       => $expenseTypes->items(),
        'pagination' => [
            'current_page' => $expenseTypes->currentPage(),
            'last_page'    => $expenseTypes->lastPage(),
            'per_page'     => $expenseTypes->perPage(),
            'total'        => $expenseTypes->total(),
            'from'         => $expenseTypes->firstItem(),
            'to'           => $expenseTypes->lastItem(),
        ]
    ]);
}
    /* -------------------------------------------------
     | 🔹 Show Expense Type
     -------------------------------------------------*/
    public function show($id)
    {
        $expenseType = ExpenseType::where('id', $id)
            ->where('isDeleted', 0)
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data'   => $expenseType,
        ]);
    }

    /* -------------------------------------------------
     | 🔹 Update Expense Type
     -------------------------------------------------*/
    public function update(Request $request, $id)
    {
        $validated = $request->validate(
            [
                'type' => 'required|string|max:255|unique:expense_types,type,' . $id,
            ],
            [],
            ['type' => 'expense type']
        );

        $expenseType = ExpenseType::where('id', $id)
            ->where('isDeleted', 0)
            ->firstOrFail();

        $expenseType->update([
            'type' => $validated['type'],
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Expense type updated successfully.',
            'data'    => $expenseType->fresh(),
        ]);
    }

    /* -------------------------------------------------
     | 🔹 Delete Expense Type (Safe)
     -------------------------------------------------*/
    public function destroy($id)
    {
        $expenseType = ExpenseType::findOrFail($id);

        $isUsed = Expense::where('expense_type_id', $id)
            ->where('isDeleted', 0)
            ->exists();

        if ($isUsed) {
            return response()->json([
                'status'  => false,
                'message' => 'This expense type is already used in expenses and cannot be deleted.',
            ], 422);
        }

        $expenseType->update(['isDeleted' => 1]);

        return response()->json([
            'status'  => true,
            'message' => 'Expense type deleted successfully.',
        ]);
    }
}
