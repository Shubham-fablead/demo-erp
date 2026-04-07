<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\BankMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;



class BankMasterController extends Controller
{

    /**
     * Display a listing of the resource.
     */


    // public function getData(Request $request)
    // {

    //     $user         = Auth::guard('api')->user();
    //     $userBranchId = $user->id ?? null;

    //     // if (!empty($request->selectedSubAdminId)) {
    //     //     $userBranchId = $request->selectedSubAdminId;
    //     // }
    //     if ($user->role === 'staff' && $user->branch_id) {
    //         $userBranchId = $user->branch_id;
    //     } elseif (! empty($request->selectedSubAdminId)) {
    //         $userBranchId = $request->selectedSubAdminId;
    //     } else {
    //         $userBranchId = $user->id;
    //     }
    //     // $branchId = $this->resolveBranchId($request);

    //     $banks = BankMaster::where('isDeleted', 0)
    //         ->where('branch_id', $userBranchId)
    //         ->orderBy('id', 'desc')
    //         ->get();

    //     return response()->json([
    //         'status' => true,
    //         'data' => $banks
    //     ]);
    // }

    public function getData(Request $request)
    {
        $user = Auth::guard('api')->user();
        $userBranchId = $user->id ?? null;

        if ($user->role === 'staff' && $user->branch_id) {
            $userBranchId = $user->branch_id;
        } elseif (!empty($request->selectedSubAdminId)) {
            $userBranchId = $request->selectedSubAdminId;
        } else {
            $userBranchId = $user->id;
        }

        $perPage = $request->get('per_page', 10);
        $search = $request->get('search', '');

        $query = BankMaster::where('isDeleted', 0)
            ->where('branch_id', $userBranchId)
            ->orderBy('id', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('bank_name', 'LIKE', "%{$search}%")
                    ->orWhere('account_number', 'LIKE', "%{$search}%")
                    ->orWhere('opening_balance', 'LIKE', "%{$search}%")
                    ->orWhere('ifsc_code', 'LIKE', "%{$search}%")
                    ->orWhere('branch_name', 'LIKE', "%{$search}%");
            });
        }

        $banks = $query->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $banks->items(),
            'pagination' => [
                'current_page' => $banks->currentPage(),
                'last_page' => $banks->lastPage(),
                'per_page' => $banks->perPage(),
                'total' => $banks->total(),
                'from' => $banks->firstItem(),
                'to' => $banks->lastItem(),
            ]
        ]);
    }


    public function store(Request $request)
    {
        $authUser = Auth::guard('api')->user();
        $userId   = $authUser->id;

        // 🔹 Decide branch ID properly
        if ($authUser->role == 'staff' && $authUser->branch_id) {
            $userBranchId = $authUser->branch_id; // staff uses their branch_id
        } elseif ($authUser->role == 'sub-admin') {
            $userBranchId = $authUser->id; // sub-admin uses own id
        } elseif ($authUser->role == 'admin' && ! empty($request->selectedSubAdminId)) {
            $userBranchId = (int) $request->selectedSubAdminId; // admin chooses sub-admin
        } else {
            $userBranchId = $authUser->id; // fallback to logged in user's id
        }

        $validatedData = $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:255',
            'branch_id'     => 'nullable|numeric',
            'ifsc_code' => 'required|string|max:255',
            'branch_name' => 'required|string|max:255',
            'opening_balance' => 'required',
            'status' => 'required|in:0,1'

        ], [
            'bank_name.required' => 'Bank name is required.',
            'account_number.required' => 'Account number is required.',
            'ifsc_code.required' => 'IFSC code is required.',
            'branch_name.required' => 'Branch name is required.',
        ]);
        $validatedData['branch_id'] = $userBranchId;

        $bank = BankMaster::create($validatedData);

        return response()->json([
            'status' => true,
            'message' => 'Bank added successfully.',
            'data' => $bank
        ]);
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $bank = BankMaster::findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $bank
        ]);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $bank = BankMaster::findOrFail($id);

        $validatedData = $request->validate([
            'bank_name' => 'required|string|max:255',
            'branch_id'     => 'nullable|numeric',
            'account_number' => 'required|string|max:255',
            'ifsc_code' => 'required|string|max:255',
            'branch_name' => 'required|string|max:255',
            'opening_balance' => 'required',
            'status' => 'required|in:0,1'
        ]);

        $bank->update($validatedData);

        return response()->json([
            'status' => true,
            'message' => 'Bank updated successfully.',
            'data' => $bank
        ]);
    }



    // public function destroy($id)
    // {
    //     $bank = BankMaster::findOrFail($id);

    //     // Check if the bank has any related entries (example: OrderCashBankBookEntry)
    //     $hasEntries = $bank->entries()->exists();

    //     if ($hasEntries) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'This bank has related entries and cannot be deleted.'
    //         ], 400);
    //     }

    //     // Soft delete by updating isDeleted
    //     $bank->update(['isDeleted' => 1]);

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Bank deleted successfully.'
    //     ], 200);
    // }
    public function destroy($id)
    {
        $bank = BankMaster::findOrFail($id);

        $bank->update(['isDeleted' => 1]);

        return response()->json([
            'status'  => true,
            'message' => 'Bank deleted successfully',
        ], 200);
    }
}
