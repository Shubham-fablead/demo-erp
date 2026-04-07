<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\CreditNotesType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class CreditNotesTypeController extends Controller
{
    /* -------------------------------------------------
     | 🔹 Helpers
     -------------------------------------------------*/

    private function resolveBranchId(Request $request)
    {
        $user = Auth::guard('api')->user();
        $role = strtolower($user->role);

        return match ($role) {
            'sub-admin' => $user->id,
            'staff'     => $user->branch_id,
            'admin'     => $request->selectedSubAdminId ?: $user->id,
            default     => $user->id,
        };
    }

    /* -------------------------------------------------
     | 🔹 Get All Credit Notes
     -------------------------------------------------*/
    // public function index(Request $request)
    // {
    //     $branchId = $this->resolveBranchId($request);

    //     $creditNotes = CreditNotesType::where('isdeleted', 0)
    //         ->where('branch_id', $branchId)
    //         ->orderBy('created_at', 'desc')
    //         ->get();

    //     return response()->json([
    //         'status' => true,
    //         'creditNotes' => $creditNotes
    //     ], 200);
    // }
    public function index(Request $request)
{
    $branchId = $this->resolveBranchId($request);
    $perPage  = $request->get('per_page', 10);
    $search   = $request->get('search', '');

    $query = CreditNotesType::where('isdeleted', 0)
        ->where('branch_id', $branchId);

    if (!empty($search)) {
        $query->where('type_name', 'LIKE', "%{$search}%");
    }

    $creditNotes = $query->orderBy('created_at', 'desc')->paginate($perPage);

    return response()->json([
        'status'      => true,
        'creditNotes' => $creditNotes->items(),
        'pagination'  => [
            'current_page' => $creditNotes->currentPage(),
            'last_page'    => $creditNotes->lastPage(),
            'per_page'     => $creditNotes->perPage(),
            'total'        => $creditNotes->total(),
            'from'         => $creditNotes->firstItem(),
            'to'           => $creditNotes->lastItem(),
        ]
    ], 200);
}

    /* -------------------------------------------------
     | 🔹 Create Credit Note
     -------------------------------------------------*/
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'type_name'    => 'required|string|max:255',
            'sub_admin_id' => 'nullable|numeric',
        ])->validate();

        $branchId = $this->resolveBranchId($request);

        $creditNote = CreditNotesType::create([
            'type_name' => $validated['type_name'],
            'branch_id' => $validated['sub_admin_id'] ?? $branchId,
            'isdeleted' => 0,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Credit Note Type created successfully',
            'data'    => $creditNote
        ], 201);
    }

    public function show($id)
    {
        $creditNote = CreditNotesType::find($id);
        if (!$creditNote) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $creditNote], 200);
    }

    public function update(Request $request, $id)
    {
        $validated = Validator::make($request->all(), [
            'type_name' => 'required|string|max:255',
        ])->validate();

        $creditNote = CreditNotesType::findOrFail($id);

        $creditNote->update([
            'type_name' => $validated['type_name'],
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Credit Note Type updated successfully',
            'data'    => $creditNote
        ], 200);
    }

    public function destroy($id)
    {
        $creditNote = CreditNotesType::findOrFail($id);

        $creditNote->update(['isdeleted' => 1]);

        return response()->json([
            'status'  => true,
            'message' => 'Credit Note Type deleted successfully'
        ], 200);
    }
}
