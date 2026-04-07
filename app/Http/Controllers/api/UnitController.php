<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UnitController extends Controller
{


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

        $branchId = $userBranchId;

        $validated = Validator::make($request->all(), [
            'unitname' => 'required|unique:units,unit_name|string|max:255',
        ]);
        $validated->setAttributeNames(['unitname' => 'Unit Name']);

        if ($validated->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validated->errors()
            ], 422);
        }

        $unit = Unit::create([
            'unit_name'  => $request->unitname,
            'is_delete'  => 0,
            'created_by' => $branchId,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Unit created successfully',
            'unit'    => $unit
        ], 201);
    }

    // public function index(Request $request)
    // {
    //     $authUser = Auth::guard('api')->user();

    //     // STAFF
    //     if ($authUser->role === 'staff' && !empty($authUser->branch_id)) {
    //         $branchId = $authUser->branch_id;
    //     }

    //     // SUB ADMIN
    //     elseif ($authUser->role === 'sub-admin') {
    //         $branchId = $authUser->id;
    //     }

    //     // ADMIN
    //     elseif ($authUser->role === 'admin') {

    //         if (!empty($request->selectedSubAdminId)) {
    //             $branchId = (int) $request->selectedSubAdminId;
    //         } else {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Please select branch'
    //             ], 400);
    //         }
    //     }

    //     // DEFAULT
    //     else {
    //         $branchId = $authUser->id;
    //     }

    //     $units = Unit::where('is_delete', 0)
    //         ->where('created_by', $branchId)
    //         ->latest()
    //         ->get();

    //     return response()->json([
    //         'status' => true,
    //         'units'  => $units
    //     ], 200);
    // }
    public function index(Request $request)
{
    $authUser = Auth::guard('api')->user();

    // Determine branchId (same logic as before)
    if ($authUser->role === 'staff' && !empty($authUser->branch_id)) {
        $branchId = $authUser->branch_id;
    } elseif ($authUser->role === 'sub-admin') {
        $branchId = $authUser->id;
    } elseif ($authUser->role === 'admin') {
        if (!empty($request->selectedSubAdminId)) {
            $branchId = (int) $request->selectedSubAdminId;
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Please select branch'
            ], 400);
        }
    } else {
        $branchId = $authUser->id;
    }

    // Pagination and search parameters
    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);
    $search = $request->input('search');

    $query = Unit::where('is_delete', 0)
        ->where('created_by', $branchId);

    if (!empty($search)) {
        $query->where('unit_name', 'LIKE', "%{$search}%");
    }

    $units = $query->latest('id')->paginate($perPage, ['*'], 'page', $page);

    return response()->json([
        'status' => true,
        'data' => $units->items(),
        'pagination' => [
            'current_page' => $units->currentPage(),
            'last_page' => $units->lastPage(),
            'per_page' => $units->perPage(),
            'total' => $units->total(),
            'next_page_url' => $units->nextPageUrl(),
            'prev_page_url' => $units->previousPageUrl(),
        ]
    ], 200);
}

    public function update(Request $request, $id)
    {
        $authUser = Auth::guard('api')->user();

        if ($authUser->role === 'staff' && !empty($authUser->branch_id)) {
            $branchId = $authUser->branch_id;
        } elseif ($authUser->role === 'sub-admin') {
            $branchId = $authUser->id;
        } elseif ($authUser->role === 'admin' && !empty($request->selectedSubAdminId)) {
            $branchId = (int) $request->selectedSubAdminId;
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Branch not selected'
            ], 400);
        }

        $validated = Validator::make($request->all(), [
            'unitname' => [
                'required',
                'string',
                'max:255',
                Rule::unique('units', 'unit_name')
                    ->ignore($id) // <- ignore current unit
                    ->where(function ($query) {
                        return $query->where('is_delete', 0);
                    }),
            ],
        ], [
            'unitname.required' => 'Unit Name is required.',
            'unitname.unique'   => 'This Unit Name already exists.',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validated->errors()
            ], 422);
        }

        $unit = Unit::where('id', $id)
            ->where('created_by', $branchId)
            ->first();

        if (!$unit) {
            return response()->json([
                'status' => false,
                'message' => 'Unit not found'
            ], 404);
        }

        $unit->update([
            'unit_name' => $request->unitname
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Unit updated successfully'
        ], 200);
    }

    public function destroy($id)
    {
        $authUser = Auth::guard('api')->user();

        if ($authUser->role === 'staff' && !empty($authUser->branch_id)) {
            $branchId = $authUser->branch_id;
        } elseif ($authUser->role === 'sub-admin') {
            $branchId = $authUser->id;
        } elseif ($authUser->role === 'admin') {
            if (!empty(request()->selectedSubAdminId)) {
                $branchId = (int) request()->selectedSubAdminId;
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Please select branch'
                ], 400);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Branch not selected'
            ], 400);
        }

           $hasProducts = Product::where('unit_id', $id)
            ->where('isDeleted', 0)
            ->exists();

        if ($hasProducts) {
            return response()->json([
                'status'  => false,
                'message' => 'This unit is associated with products and cannot be deleted.',
            ], 400);
        }

        $unit = Unit::where('id', $id)
            ->where('created_by', $branchId)
            ->first();

        if (!$unit) {
            return response()->json([
                'status' => false,
                'message' => 'Unit not found'
            ], 404);
        }

        $unit->update(['is_delete' => 1]);

        return response()->json([
            'status' => true,
            'message' => 'Unit deleted successfully'
        ], 200);
    }
}
