<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\CreditNoteItem;
use App\Models\Order;
use App\Models\PurchaseInvoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CreditNoteItemApiController extends Controller
{
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


    public function index(Request $request)
    {
        $branchId = $this->resolveBranchId($request);
        $user = Auth::guard('api')->user();

        $query = CreditNoteItem::with(['order.user.userDetail', 'purchaseInvoice.vendor.userDetail', 'creditNote'])
            ->where('isDeleted', 0);

        if ($user && ($user->role !== 'admin' || $request->filled('selectedSubAdminId'))) {
            $query->where('branch_id', $branchId);
        }

        // ✅ Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('order', function ($o) use ($search) {
                    $o->where('order_number', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('purchaseInvoice', function ($pi) use ($search) {
                    $pi->where('invoice_number', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('order.user', function ($u) use ($search) {
                    $u->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('purchaseInvoice.vendor', function ($v) use ($search) {
                    $v->where('name', 'LIKE', "%{$search}%");
                });
            });
        }

        // Calculate total settlement for the filtered result (before pagination)
        $totalSettlement = (float) $query->sum('settlement_amount');

        // Pagination
        $perPage = $request->input('per_page', 10);
        $page    = $request->input('page', 1);

        $itemsPaginated = $query->orderBy('created_at', 'desc')->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'data' => $itemsPaginated->items(),
            'pagination' => [
                'current_page' => $itemsPaginated->currentPage(),
                'last_page'    => $itemsPaginated->lastPage(),
                'per_page'     => $itemsPaginated->perPage(),
                'total'        => $itemsPaginated->total(),
            ],
            'total_settlement' => number_format($totalSettlement, 2, '.', '')
        ]);
    }

    public function getPurchaseDetails(Request $request, $invoiceNumber)
    {
        $branchId = $this->resolveBranchId($request);
        
        $invoice = PurchaseInvoice::where('invoice_number', $invoiceNumber)
            ->where('isDeleted', 0)
            ->first();

        if (!$invoice) {
            return response()->json(['error' => 'Purchase Invoice not found'], 404);
        }

        $vendor = User::find($invoice->vendor_id);

        $purchaseData = [
            'id'               => $invoice->id,
            'vendor_id'        => $invoice->vendor_id,
            'invoice_number'   => $invoice->invoice_number,
            'vendor_name'      => $vendor->name ?? 'N/A',
            'total_amount'     => $invoice->grand_total ?? 0,
            'remaining_amount' => $invoice->remaining_amount ?? 0,
            'paid_amount'      => $invoice->paid ?? 0,
        ];

        return response()->json([
            'purchase' => $purchaseData
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
        // dd($userBranchId);
        $validator = Validator::make($request->all(), [
            'transaction_type' => 'required|in:receipt,payment',
            'order_id' => 'required_if:transaction_type,receipt',
            'purchase_id' => 'required_if:transaction_type,payment',
            'user_id' => 'required',
            'credite_note_id' => 'required|exists:credit_notes_type,id',
            'total_amt' => 'required|numeric',
            'remaining_amt' => 'required|numeric',
'settlement_amount' => 'bail|required|numeric|min:0|lte:remaining_amt',
            'total_paid' => 'required|numeric',
            
            'total' => 'required|numeric',
            'reason' => 'required|string',
        ], [
            'order_id.required_if' => 'Please select an Order Number.',
            'purchase_id.required_if' => 'Please select an Invoice Number.',
            'credite_note_id.required' => 'Please select a Credit Note Type.',
            'settlement_amount.required' => 'Please enter settlement amount.',
    'settlement_amount.min' => 'Settlement amount cannot be negative.',
    'settlement_amount.lte' => 'The settlement amount field must be less than or equal to remaining amount.',
            'reason.required' => 'Please provide a reason.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $creditNoteItem = CreditNoteItem::create([
                'type_id' => $request->transaction_type,
                'order_id' => $request->transaction_type === 'receipt' ? $request->order_id : null,
                'purchase_id' => $request->transaction_type === 'payment' ? $request->purchase_id : null,
                'user_id' => $request->user_id,
                'credite_note_id' => $request->credite_note_id,
                'branch_id'          => $userBranchId ?? $userId, // ✅ REQUIRED
                'total_amt' => $request->total_amt,
                'remaining_amt' => $request->remaining_amt,
                'total_paid' => $request->total_paid,
                'settlement_amount' => $request->settlement_amount,
                'total' => $request->total,
                'reason' => $request->reason,
                'isDeleted' => 0,
            ]);


            return response()->json([
                'status' => true,
                'message' => 'Credit Note Item created successfully!',
                'data' => $creditNoteItem
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $branchId = $this->resolveBranchId($request);
        $user = Auth::guard('api')->user();

        $query = CreditNoteItem::with(['order.user.userDetail', 'purchaseInvoice.vendor.userDetail', 'creditNote']);

        if ($user && ($user->role !== 'admin' || $request->filled('selectedSubAdminId'))) {
            $query->where('branch_id', $branchId);
        }

        $creditNoteItem = $query->find($id);

        if (!$creditNoteItem) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }
        return response()->json(['status' => true, 'data' => $creditNoteItem], 200);
    }

    public function update(Request $request, $id)
    {
        $branchId = $this->resolveBranchId($request);
        $user = Auth::guard('api')->user();

        $query = CreditNoteItem::query();

        if ($user && ($user->role !== 'admin' || $request->filled('selectedSubAdminId'))) {
            $query->where('branch_id', $branchId);
        }

        $creditNoteItem = $query->find($id);

        if (!$creditNoteItem) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'transaction_type' => 'required|in:receipt,payment',
            'order_id' => 'required_if:transaction_type,receipt',
            'purchase_id' => 'required_if:transaction_type,payment',
            'user_id' => 'required',
            'credite_note_id' => 'required|exists:credit_notes_type,id',
            'total_amt' => 'required|numeric',
            'remaining_amt' => 'required|numeric',
            'total_paid' => 'required|numeric',
            'remaining_amt' => 'required|numeric',
'settlement_amount' => 'bail|required|numeric|min:0|lte:remaining_amt',
            'total' => 'required|numeric',
            'reason' => 'required|string',
        ], [
            'order_id.required_if' => 'Please select an Order Number.',
            'purchase_id.required_if' => 'Please select an Invoice Number.',
            'credite_note_id.required' => 'Please select a Credit Note Type.',
            'settlement_amount.required' => 'Please enter settlement amount.',
    'settlement_amount.min' => 'Settlement amount cannot be negative.',
    'settlement_amount.lte' => 'Settlement amount cannot exceed remaining amount.', 
            'reason.required' => 'Please provide a reason.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $creditNoteItem->update([
                'type_id' => $request->transaction_type,
                'order_id' => $request->transaction_type === 'receipt' ? $request->order_id : null,
                'purchase_id' => $request->transaction_type === 'payment' ? $request->purchase_id : null,
                'user_id' => $request->user_id,
                'credite_note_id' => $request->credite_note_id,
                'total_amt' => $request->total_amt,
                'remaining_amt' => $request->remaining_amt,
                'total_paid' => $request->total_paid,
                'settlement_amount' => $request->settlement_amount,
                'total' => $request->total,
                'reason' => $request->reason,
            ]);

            return response()->json([
                'status' => true,
                'message' => 'Credit Note Item updated successfully!',
                'data' => $creditNoteItem
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $branchId = $this->resolveBranchId($request);
        $user = Auth::guard('api')->user();

        $query = CreditNoteItem::query();

        if ($user && ($user->role !== 'admin' || $request->filled('selectedSubAdminId'))) {
            $query->where('branch_id', $branchId);
        }

        $creditNoteItem = $query->find($id);

        if (!$creditNoteItem) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        try {
            $creditNoteItem->update(['isDeleted' => 1]);

            return response()->json([
                'status' => true,
                'message' => 'Credit Note Item deleted successfully!',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}
