<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDetail;
use App\Models\Product;
use App\Models\TaxRate;
use App\Models\Order;
use App\Models\Purchases;
use App\Models\PurchaseInvoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TaxRateController extends Controller
{
    // Fetch all tax rates
    public function index(Request $request)
    {
        $user = Auth::guard('api')->user();
        $role = $user->role;
        $userBranchId = $user->id;
        $BranchId = $user->branch_id ?? $userBranchId;

        $selectedSubAdminId = $request->query('selectedSubAdminId');
        $search = trim((string) $request->query('search', ''));
        $perPage = (int) $request->query('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        $query = TaxRate::where('isDeleted', 0);

        if ($role === 'sub-admin') {
            // Sub-admin: show only their own branch's data
            $query->where('branch_id', $userBranchId);
        } elseif ($role === 'admin' && !empty($selectedSubAdminId)) {
            // Admin with filter: show selected sub-admin's branch data
            $subAdmin = User::find($selectedSubAdminId);
            if ($subAdmin) {
                $query->where('branch_id', $subAdmin->id);
            }
        } elseif ($role === 'staff') {
            $query->where('branch_id', $BranchId);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('tax_name', 'like', "%{$search}%")
                    ->orWhere('tax_rate', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $taxRates = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json([
            'status' => true,
            'taxRates' => $taxRates->items(),
            'data' => $taxRates->items(),
            'pagination' => [
                'current_page' => $taxRates->currentPage(),
                'last_page' => $taxRates->lastPage(),
                'per_page' => $taxRates->perPage(),
                'total' => $taxRates->total(),
            ],
        ], 200);
    }



    // Store a new tax rate
    public function store(Request $request)
    {
        $user = Auth::guard('api')->user();
        $role = $user->role;
        $userBranchId = $user->id;
        $BrachIdUser = $request->selectedSubAdminId;
        if ($request->selectedSubAdminId === 'null' || !empty($request->selectedSubAdminId)) {
            $BrachId = $BrachIdUser;
        } else {
            $BrachId = $userBranchId;
        }
        $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric',
            'status' => 'required|in:active,inactive',
        ]);

        $taxRate = TaxRate::create([
            'tax_name' => $request->name,
            'tax_rate' => $request->rate,
            'status' => $request->status,
            'branch_id' => $BrachId,
            'isDeleted' => 0
        ]);

        return response()->json(['status' => true, 'message' => 'Tax rate added successfully!', 'taxRate' => $taxRate], 201);
    }

    // Fetch a specific tax rate for editing
    public function edit($id)
    {
        $taxRate = TaxRate::find($id);
        if (!$taxRate) {
            return response()->json(['message' => 'Tax rate not found'], 404);
        }
        return response()->json(['status' => true, 'taxRate' => $taxRate], 200);
    }

    // Update tax rate
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric',
            'status' => 'required|in:active,inactive',
        ]);

        $taxRate = TaxRate::find($id);
        if (!$taxRate) {
            return response()->json(['message' => 'Tax rate not found'], 404);
        }

        $taxRate->update([
            'tax_name' => $request->name,
            'tax_rate' => $request->rate,
            'status' => $request->status,
        ]);

        return response()->json(['status' => true, 'message' => 'Tax rate updated successfully!', 'taxRate' => $taxRate], 200);
    }

    // Delete tax rate
    public function destroy($id)
    {
        $taxRate = TaxRate::find($id);

        if (!$taxRate) {
            return response()->json(['status' => false, 'message' => 'Tax rate not found'], 404);
        }

        // Check if tax_id array in Orders contains the ID
        $salesAssociated = Order::whereJsonContains('tax_id', (int) $id)->exists();

        // Check if taxes JSON in Purchases contains an object with matching ID
        $purchasesAssociated = PurchaseInvoice::whereJsonContains('taxes', ['id' => (int) $id])->exists();

        if ($salesAssociated) {
            return response()->json([
                'status' => false,
                'error' => 'Tax rate is associated with existing sales and cannot be deleted.'
            ], 409);
        }

        if ($purchasesAssociated) {
            return response()->json([
                'status' => false,
                'error' => 'Tax rate is associated with existing purchases and cannot be deleted.'
            ], 409);
        }

        // Soft delete: mark isDeleted as 1
        $taxRate->isDeleted = 1;
        $taxRate->save();

        return response()->json([
            'status' => true,
            'message' => 'Tax rate soft deleted successfully!',
        ], 200);
    }
}
