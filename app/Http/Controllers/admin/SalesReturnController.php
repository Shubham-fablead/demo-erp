<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\SalesReturn;
use App\Models\TaxRate;
use Illuminate\Http\Request;

class SalesReturnController extends Controller
{
    private function getBranchContext(): array
    {
        $user = auth()->user();

        $userId = $user->id;
        $role = $user->role;
        $userBranch = $user->branch_id;
        $selectedBranch = session('selectedSubAdminId');

        if ($role === 'admin' && $selectedBranch) {
            $branchId = $selectedBranch;
        } elseif ($role === 'sub-admin') {
            $branchId = $userId;
        } elseif ($role === 'staff') {
            $branchId = $userBranch;
        } else {
            $branchId = $userId;
        }

        return [$user, $userId, $role, $branchId];
    }

    public function createsales_return(Request $request)
    {
        [, $userId, $role, $branchId] = $this->getBranchContext();

        if ($role === 'staff') {
            $invoiceNumbers = Order::where('isDeleted', 0)
                ->where('quotation_status', 'sales')
                ->where('created_by', $userId)
                ->whereHas('user', function ($query) {
                    $query->where('isDeleted', 0);
                })
                ->whereHas('orderItems', function ($query) {
                    $query->whereRaw('order_items.quantity > (SELECT IFNULL(SUM(quantity), 0) FROM sales_return_items WHERE sales_return_items.order_item_id = order_items.id)');
                })
                ->orderBy('order_number', 'desc')
                ->pluck('order_number');
        } else {
            $invoiceNumbers = Order::where('isDeleted', 0)
                ->where('quotation_status', 'sales')
                ->where('branch_id', $branchId)
                ->whereHas('user', function ($query) {
                    $query->where('isDeleted', 0);
                })
                ->whereHas('orderItems', function ($query) {
                    $query->whereRaw('order_items.quantity > (SELECT IFNULL(SUM(quantity), 0) FROM sales_return_items WHERE sales_return_items.order_item_id = order_items.id)');
                })
                ->orderBy('order_number', 'desc')
                ->pluck('order_number');
        }

        $settings = \DB::table('settings')->where('branch_id', $branchId)->first();
        $currencySymbol = $settings->currency_symbol ?? '?';
        $currencyPosition = $settings->currency_position ?? 'left';

        $TaxRate = TaxRate::where('status', 'active')->where('branch_id', $branchId)->where('isDeleted', 0)->get();

        return view('salesreturn/createsalesreturn', compact(
            'invoiceNumbers',
            'TaxRate',
            'currencySymbol',
            'currencyPosition'
        ));
    }

    public function createsales_returns(Request $request)
    {
        return view('salesreturn/createsalesreturns');
    }

    public function editsales_return(Request $request)
    {
        return view('salesreturn/editsalesreturn');
    }

    public function editsales_returns(Request $request)
    {
        return view('salesreturn/editsalesreturns');
    }

    public function salesreturn_list(Request $request)
    {
        [, , , $branchId] = $this->getBranchContext();

        $settings = \DB::table('settings')->where('branch_id', $branchId)->first();
        $currencySymbol = $settings->currency_symbol ?? '?';
        $currencyPosition = $settings->currency_position ?? 'left';

        return view('salesreturn/salesreturnlist', compact(
            'currencySymbol',
            'currencyPosition'
        ));
    }

    public function salesreturn_list_data(Request $request)
    {
        [, $userId, $role, $branchId] = $this->getBranchContext();

        $salesReturnsQuery = SalesReturn::with(['order.user:id,name', 'items:id,sales_return_id,quantity'])
            ->orderBy('id', 'desc');

        if ($role === 'staff') {
            $salesReturnsQuery->where('created_by', $userId);
        } else {
            $salesReturnsQuery->where('branch_id', $branchId);
        }

        $salesReturns = $salesReturnsQuery->get();

        $settings = \DB::table('settings')->where('branch_id', $branchId)->first();
        $currencySymbol = $settings->currency_symbol ?? '?';
        $currencyPosition = $settings->currency_position ?? 'left';

        $data = $salesReturns->map(function ($salesReturn) {
            return [
                'id' => $salesReturn->id,
                'return_number' => $salesReturn->return_number ?? '-',
                'order_number' => $salesReturn->order->order_number ?? '-',
                'date' => optional($salesReturn->created_at)->format('d M Y, h:i A'),
                'customer' => $salesReturn->order->user->name ?? '-',
                'items_count' => (int) $salesReturn->items->count(),
                'return_qty' => (int) $salesReturn->items->sum('quantity'),
                'subtotal' => (float) ($salesReturn->subtotal ?? 0),
                'tax_amount' => (float) ($salesReturn->tax_amount ?? 0),
                'discount' => (float) ($salesReturn->discount ?? 0),
                'discount_amount' => (float) ($salesReturn->discount_amount ?? 0),
                'total_amount' => (float) ($salesReturn->total_amount ?? 0),
            ];
        })->values();

        return response()->json([
            'status' => true,
            'currency_symbol' => $currencySymbol,
            'currency_position' => $currencyPosition,
            'data' => $data,
        ]);
    }

    public function salesreturn_lists(Request $request)
    {
        return $this->salesreturn_list($request);
    }
}
