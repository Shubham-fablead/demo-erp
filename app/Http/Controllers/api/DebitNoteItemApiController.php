<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\DebitNoteItem;
use App\Models\PurchaseInvoice;
use App\Models\Order;
use App\Models\PaymentStore;
use App\Models\CreditNotesType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DebitNoteItemApiController extends Controller
{
    private function resolveBranchId(Request $request)
    {
        $user = Auth::guard('api')->user();
        if (!$user) return null;
        $role = strtolower($user->role);

        $selectedSubAdminId = $request->selectedSubAdminId;
        if ($selectedSubAdminId === 'null' || $selectedSubAdminId === 'undefined') {
            $selectedSubAdminId = null;
        }

        return match ($role) {
            'sub-admin' => $user->id,
            'staff'     => $user->branch_id,
            'admin'     => $selectedSubAdminId ?: null,
            default     => $user->id,
        };
    }

    public function index(Request $request)
    {
        $branchId = $this->resolveBranchId($request);
        $user = Auth::guard('api')->user();

        $query = DebitNoteItem::with(['creditNoteType', 'order.user.userDetail', 'purchaseInvoice.vendor.userDetail'])
            ->where(function($q) {
                $q->where('isDeleted', 0)->orWhereNull('isDeleted');
            });
            
        if ($user && ($user->role !== 'admin' || $request->filled('selectedSubAdminId'))) {
            $query->where('branch_id', $branchId);
        }

        // ✅ Apply search filter
        if ($request->filled('search')) {
            $search = trim((string) $request->search);
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
                })
                ->orWhereHas('creditNoteType', function ($t) use ($search) {
                    $t->where('type_name', 'LIKE', "%{$search}%");
                })
                ->orWhere('transaction_type', 'LIKE', "%{$search}%")
                ->orWhere('grand_total', 'LIKE', "%{$search}%")
                ->orWhere('settlement_amount', 'LIKE', "%{$search}%")
                ->orWhere('total', 'LIKE', "%{$search}%");
            });
        }

        // Calculate total settlement for the filtered result (before pagination)
        $totalSettlement = (float) $query->sum('settlement_amount');

        // Pagination
        $perPage = (int) $request->input('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;
        $page    = $request->input('page', 1);

        $itemsPaginated = $query->orderBy('id', 'desc')->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => 'success',
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

    public function getCreateData(Request $request)
    {
        $branchId = $this->resolveBranchId($request);

        $creditNoteTypes = CreditNotesType::where('isdeleted', 0);
        $invoices = PurchaseInvoice::where('isDeleted', 0)->orderBy('id', 'desc');
        $orders = Order::where('isDeleted', 0)->orderBy('id', 'desc');

        if ($branchId) {
            $creditNoteTypes->where('branch_id', $branchId);
            $invoices->where('branch_id', $branchId);
            $orders->where('branch_id', $branchId);
        }

        return response()->json([
            'status' => 'success',
            'creditNoteTypes' => $creditNoteTypes->get(),
            'invoices' => $invoices->get(),
            'orders' => $orders->get()
        ]);
    }

    public function getOrderDetails(Request $request, $order_number)
    {
        $order = Order::with('user.userDetail')
            ->where('order_number', $order_number)
            ->where('isDeleted', 0)
            ->first();

        if ($order) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $order->id,
                    'user_id' => $order->user_id,
                    'customer_name' => $order->user->name ?? 'N/A',
                    'total_amount' => $order->total_amount,
                    'paid_amount' => $order->paid ?? 0,
                    'remaining_amount' => $order->remaining_amount,
                ]
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
    }

    public function getInvoiceDetails(Request $request, $invoice_number)
    {
        $invoice = PurchaseInvoice::with('vendor.userDetail')
            ->where('invoice_number', $invoice_number)
            ->where('isDeleted', 0)
            ->first();

        if ($invoice) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $invoice->id,
                    'vendor_id' => $invoice->vendor_id,
                    'vendor_name' => $invoice->vendor->name ?? 'N/A',
                    'total_amount' => $invoice->grand_total,
                    'paid_amount' => $invoice->paid ?? 0,
                    'remaining_amount' => $invoice->remaining_amount,
                ]
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Invoice not found'], 404);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_type' => 'required|in:receipt,payment',
            'order_id' => 'required_if:transaction_type,receipt',
            'purchase_id' => 'required_if:transaction_type,payment',
            'credit_note_type' => 'required',
            'settlement_amount' => 'required|numeric|min:0|lte:remaining_amount',
            'reason' => 'required',
        ], [
            'order_id.required_if' => 'Please select an Order Number.',
            'purchase_id.required_if' => 'Please select an Invoice Number.',
            'credit_note_type.required' => 'Please select a Debit Note Type.',
            'reason.required' => 'Please provide a reason.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $user = Auth::guard('api')->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }

        $branchId = $this->resolveBranchId($request);

        $debitNote = new DebitNoteItem();
        $debitNote->user_id = $user->id;
        $debitNote->branch_id = $branchId ?: $user->id;
        $debitNote->transaction_type = $request->transaction_type;
        $debitNote->order_id = $request->transaction_type === 'receipt' ? $request->order_id : null;
        $debitNote->purchase_id = $request->transaction_type === 'payment' ? $request->purchase_id : null;
        $debitNote->create_note_id = $request->credit_note_type;
        $debitNote->invoice_number = $request->invoice_number;
        $debitNote->grand_total = $request->total_amount;
        $debitNote->total_paid = $request->paid_amount;
        $debitNote->remaning_amount = $request->remaining_amount;
        $debitNote->settlement_amount = $request->settlement_amount;
        $debitNote->total = $request->final_total;
        $debitNote->reason = $request->reason;
        $debitNote->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Debit Note created successfully',
            'data' => $debitNote
        ]);
    }

    public function show($id)
    {
        $item = DebitNoteItem::with(['creditNoteType', 'order.user.userDetail', 'purchaseInvoice.vendor.userDetail'])->find($id);
        if (!$item) {
            return response()->json(['status' => 'error', 'message' => 'Item not found'], 404);
        }
        return response()->json(['status' => 'success', 'data' => $item]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'transaction_type' => 'required|in:receipt,payment',
            'order_id' => 'required_if:transaction_type,receipt',
            'purchase_id' => 'required_if:transaction_type,payment',
            'credit_note_type' => 'required',
            'remaining_amount' => 'required|numeric',
'settlement_amount' => 'bail|required|numeric|min:0|lte:remaining_amount',
            'reason' => 'required',
        ], [
            'order_id.required_if' => 'Please select an Order Number.',
            'purchase_id.required_if' => 'Please select an Invoice Number.',
            'credit_note_type.required' => 'Please select a Debit Note Type.',
            'settlement_amount.required' => 'Please enter settlement amount.',
    'settlement_amount.min' => 'Settlement amount cannot be negative.',
    'settlement_amount.lte' => 'The settlement amount field must be less than or equal to remaining amount.',
            'reason.required' => 'Please provide a reason.',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $debitNote = DebitNoteItem::find($id);
        if (!$debitNote) {
            return response()->json(['status' => 'error', 'message' => 'Item not found'], 404);
        }

        $debitNote->transaction_type = $request->transaction_type;
        $debitNote->order_id = $request->transaction_type === 'receipt' ? $request->order_id : null;
        $debitNote->purchase_id = $request->transaction_type === 'payment' ? $request->purchase_id : null;
        $debitNote->create_note_id = $request->credit_note_type;
        $debitNote->invoice_number = $request->invoice_number;
        $debitNote->grand_total = $request->total_amount;
        $debitNote->total_paid = $request->paid_amount;
        $debitNote->remaning_amount = $request->remaining_amount;
        $debitNote->settlement_amount = $request->settlement_amount;
        $debitNote->total = $request->final_total;
        $debitNote->reason = $request->reason;
        $debitNote->save();

        return response()->json(['status' => 'success', 'message' => 'Debit Note updated successfully']);
    }

    public function destroy($id)
    {
        $item = DebitNoteItem::find($id);
        if (!$item) {
            return response()->json(['status' => 'error', 'message' => 'Item not found'], 404);
        }
        $item->isDeleted = 1;
        $item->save();
        return response()->json(['status' => 'success', 'message' => 'Debit Note deleted successfully']);
    }
}
