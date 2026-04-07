<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\LabourItem;
use App\Models\Sales_Labour_Items;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LabourItemController extends Controller
{
    /**
     * Get all labour items
     */
    // public function getAllLabourItems(Request $request)
    // {
    //     $user = Auth::guard('api')->user();
    //     $role = $user->role;
    //     $userId = $user->id;
    //     $createdBy = $user->created_by;

    //     // Selected Sub Admin (for admin)
    //     $selectedSubAdminId = $request->query('selectedSubAdminId') ?? $userId;

    //     $query = LabourItem::where('isDeleted', 0);

    //     if ($role === 'sub-admin') {
    //         $query->where('created_by', $userId);
    //     } elseif ($role === 'admin' && !empty($selectedSubAdminId)) {
    //         $subAdmin = User::find($selectedSubAdminId);
    //         if ($subAdmin) {
    //             $query->where('created_by', $subAdmin->id);
    //         }
    //     } elseif ($role === 'staff') {
    //         $query->where('created_by', $createdBy);
    //     }

    //     $items = $query->orderBy('id', 'desc')->get();

    //     return response()->json([
    //         'status'  => true,
    //         'message' => 'Labour Item List',
    //         'data'    => $items
    //     ], 200);
    // }
    public function getAllLabourItems(Request $request)
{
    $user = Auth::guard('api')->user();
    $role = $user->role;
    $userId = $user->id;
    $createdBy = $user->created_by;

    // Selected Sub Admin (for admin)
    $selectedSubAdminId = $request->query('selectedSubAdminId') ?? $userId;

    $query = LabourItem::where('isDeleted', 0);

    if ($role === 'sub-admin') {
        $query->where('created_by', $userId);
    } elseif ($role === 'admin' && !empty($selectedSubAdminId)) {
        $subAdmin = User::find($selectedSubAdminId);
        if ($subAdmin) {
            $query->where('created_by', $subAdmin->id);
        }
    } elseif ($role === 'staff') {
        $query->where('created_by', $createdBy);
    }

    // Pagination & search parameters
    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);
    $search = $request->input('search');

    if (!empty($search)) {
        $query->where('item_name', 'LIKE', "%{$search}%");
    }

    $items = $query->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);

    return response()->json([
        'status'  => true,
        'message' => 'Labour Item List',
        'data'    => $items->items(),
        'pagination' => [
            'current_page' => $items->currentPage(),
            'last_page'    => $items->lastPage(),
            'per_page'     => $items->perPage(),
            'total'        => $items->total(),
            'next_page_url' => $items->nextPageUrl(),
            'prev_page_url' => $items->previousPageUrl(),
        ]
    ], 200);
}

    /**
     * Add labour item
     */
    public function addLabourItem(Request $request)
    {
        $user = Auth::guard('api')->user();
        $userId = $user->id;
        $createdBy = $user->created_by;

        $targetUserId = $request->sub_admin_id ?? ($createdBy ?? $userId);

        // dd ($targetUserId);

        $validator = Validator::make($request->all(), [
            'item_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('labour_items', 'item_name')->where(function ($query) use ($targetUserId) {
                    $query->where('isDeleted', 0)
                        ->where('created_by', $targetUserId);
                }),
            ],
            'price' => [
                'required',
                'numeric',
                'min:0'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $item = LabourItem::create([
            'item_name'  => $request->item_name,
            'price'      => $request->price,
            'created_by' => $targetUserId,
            'isDeleted'  => 0,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Labour Item added successfully.',
            'data'    => $item
        ], 200);
    }

    /**
     * Update labour item
     */
    public function updateLabourItem(Request $request, $id)
    {
        $item = LabourItem::find($id);

        if (!$item || $item->isDeleted) {
            return response()->json([
                'status'  => false,
                'message' => 'Labour Item not found.'
            ], 404);
        }

        $targetUserId = $item->created_by;

        $validator = Validator::make($request->all(), [
            'item_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('labour_items', 'item_name')
                    ->ignore($id)
                    ->where(function ($query) use ($targetUserId) {
                        $query->where('isDeleted', 0)
                            ->where('created_by', $targetUserId);
                    }),
            ],
            'price' => [
                'required',
                'numeric',
                'min:0'
            ]
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $item->update([
            'item_name' => $request->item_name,
            'price'     => $request->price,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Labour Item updated successfully.',
            'data'    => $item
        ], 200);
    }

    /**
     * Soft delete labour item
     */
    public function deleteLabourItem($id)
    {
        $item = LabourItem::find($id);

        if (!$item || $item->isDeleted) {
            return response()->json([
                'status'  => false,
                'message' => 'Labour Item not found.'
            ], 404);
        }

        // Check if any sales labour item is using this labour item
        $isUsedInSales = Sales_Labour_Items::where('labour_item_id', $id)->exists();

        if ($isUsedInSales) {
            return response()->json([
                'status' => false,
                'message' => 'This labour item is associated with sales and cannot be deleted.'
            ], 409); // 409 Conflict
        }

        $item->update(['isDeleted' => 1]);

        return response()->json([
            'status'  => true,
            'message' => 'Labour Item deleted successfully.'
        ], 200);
    }
}
