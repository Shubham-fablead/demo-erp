<?php
namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function signin()
    {

        return view('signin');
    }
    public function forgetpassword()
    {

        return view('forgetpassword');
    }
    public function subadmin_session(Request $request)
    {
        // dd($request->all());
        $subAdminId = $request->input('subAdminId');
        session(['selectedSubAdminId' => $subAdminId]);
        return response()->json(['status' => 'success']);
    }

    public function dashboard()
    {
        $user = auth()->user();

        if ($user->role === 'staff') {
            return redirect()->route('auth.profile');
        }

        $selectedSubAdminId = session('selectedSubAdminId');

        // ✅ Decide BranchID based on role/session
        if (! empty($selectedSubAdminId)) {
            $BranchID = $selectedSubAdminId;
        } elseif ($user->role === 'staff' && $user->branch_id) {
            $BranchID = $user->branch_id;
        } else {
            $BranchID = $user->id;
        }

        $currentYear  = Carbon::now()->year;
        $previousYear = $currentYear - 1;
        $currentMonth = Carbon::now()->month;

        // ✅ Charts
        $salesChartThisMonth    = $this->getSalesDataByMonth($currentMonth, $BranchID);
        $purchaseChartThisMonth = $this->getPurchaseDataByMonth($currentMonth, $BranchID);

        $salesChartthisyear     = $this->getSalesDataByYear($currentYear, $BranchID);
        $salesChartpreviousyear = $this->getSalesDataByYear($previousYear, $BranchID);

        $purchaseChartthisyear     = $this->getPurchaseDataByYear($currentYear, $BranchID);
        $purchaseChartpreviousyear = $this->getPurchaseDataByYear($previousYear, $BranchID);

        // ✅ Totals (branch-wise)
        $totalPurchaseAmount = PurchaseInvoice::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->sum('grand_total');

        $totalSalesAmount = Order::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->sum('total_amount');

        $totalExpenseAmount = Expense::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->sum('amount');

        // ✅ Counts
        $customerCount = User::where('role', 'customer')
            ->where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->count();

        $vendorCount = User::where('role', 'vendor')
            ->where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->count();

        $purchaseInvoiceCount = PurchaseInvoice::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->count();

        $salesInvoiceCount = Order::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->count();

        // ✅ Recent products (branch-wise)
        $recentProducts = Product::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->latest()
            ->take(4)
            ->get();

        // ✅ Latest Sales
        $latestSales = DB::table('orders')
            ->where('orders.isDeleted', '!=', 1)
            ->where('orders.branch_id', $BranchID)

        // join ONE order item per order
            ->joinSub(
                DB::table('order_items')
                    ->selectRaw('MIN(id) as id, order_id')
                    ->where('isDeleted', '!=', 1)
                    ->groupBy('order_id'),
                'oi',
                function ($join) {
                    $join->on('orders.id', '=', 'oi.order_id');
                }
            )

            ->join('order_items', 'order_items.id', '=', 'oi.id')

            ->join('products', function ($join) {
                $join->on('order_items.product_id', '=', 'products.id')
                    ->where('products.isDeleted', '!=', 1);
            })
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')

            ->select(
                'orders.id as order_id',
                'orders.order_number',
                'orders.created_at as order_date',
                'orders.total_amount',
                'orders.payment_method',

                'products.id as product_id',
                'products.name as product_name',
                'products.SKU as product_code',
                'products.images',

                'brands.name as brand_name',
                'categories.name as category_name'
            )
            ->orderBy('orders.created_at', 'desc')
            ->limit(4)
            ->get();

        $latestPurchases = DB::table('purchase_invoice as pi')
            ->where('pi.isDeleted', '!=', 1)
            ->where('pi.branch_id', '=', $BranchID)

            // join ONE purchase item per invoice
            ->joinSub(
                DB::table('purchases')
                    ->selectRaw('MIN(id) as id, invoice_id')
                    ->where('isDeleted', '!=', 1)
                    ->groupBy('invoice_id'),
                'p',
                function ($join) {
                    $join->on('pi.id', '=', 'p.invoice_id');
                }
            )

            ->join('purchases as pur', 'pur.id', '=', 'p.id')

            ->leftJoin('products as pr', function ($join) {
                $join->on('pr.id', '=', 'pur.item')
                    ->where('pr.isDeleted', '!=', 1);
            })
            ->leftJoin('brands as br', 'pr.brand_id', '=', 'br.id')
            ->leftJoin('categories as cat', 'pr.category_id', '=', 'cat.id')

            ->select(
                'pi.id as invoice_id',
                'pi.invoice_number',
                'pi.grand_total',
                'pi.created_at as purchase_date',

                'pr.id as product_id',
                'pr.name as product_name',
                'pr.SKU as product_code',
                'pr.images',

                'br.name as brand_name',
                'cat.name as category_name'
            )
            ->orderBy('pi.created_at', 'desc')
            ->limit(4)
            ->get();

        // ✅ Monthly Sales Chart
        $salesData = DB::table('order_items')
            ->join('orders', function ($join) use ($BranchID) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->where('orders.isDeleted', '!=', 1)
                    ->where('orders.branch_id', '=', $BranchID);
            })
            ->where('order_items.isDeleted', '!=', 1)
            ->select(DB::raw("MONTH(order_items.created_at) as month"), DB::raw("SUM(order_items.total_amount) as total"))
            ->groupBy(DB::raw("MONTH(order_items.created_at)"))
            ->pluck('total', 'month')
            ->toArray();

        // ✅ Monthly Purchase Chart
        $purchasesData = DB::table('purchases')
            ->leftJoin('purchase_invoice', function ($join) use ($BranchID) {
                $join->on('purchase_invoice.id', '=', 'purchases.invoice_id')
                    ->where('purchase_invoice.isDeleted', '!=', 1)
                    ->where('purchase_invoice.branch_id', '=', $BranchID);
            })
            ->where('purchases.isDeleted', '!=', 1)
            ->select(DB::raw("MONTH(purchases.created_at) as month"), DB::raw("SUM(purchases.amount_total) as total"))
            ->groupBy(DB::raw("MONTH(purchases.created_at)"))
            ->pluck('total', 'month')
            ->toArray();

        $salesChart    = [];
        $purchaseChart = [];
        for ($m = 1; $m <= 12; $m++) {
            $salesChart[]    = (float) ($salesData[$m] ?? 0);
            $purchaseChart[] = (float) ($purchasesData[$m] ?? 0);
        }

        // ✅ Branch-specific settings
        $settings         = DB::table('settings')->where('branch_id', $BranchID)->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        return view('index', compact(
            'totalPurchaseAmount',
            'totalSalesAmount',
            'totalExpenseAmount',
            'customerCount',
            'vendorCount',
            'purchaseInvoiceCount',
            'salesInvoiceCount',
            'recentProducts',
            'latestSales',
            'latestPurchases',
            'salesChart',
            'purchaseChart',
            'currencySymbol',
            'currencyPosition',
            'salesChartthisyear',
            'salesChartpreviousyear',
            'purchaseChartthisyear',
            'purchaseChartpreviousyear',
            'salesChartThisMonth',
            'purchaseChartThisMonth',
        ));
    }

    private function getSalesDataByMonth($month, $BranchID)
    {
        $salesData = DB::table('orders')
            ->whereMonth('created_at', $month)
            ->where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->select(
                DB::raw("DAY(created_at) as day"),
                DB::raw("SUM(total_amount) as total")
            )
            ->groupBy(DB::raw("DAY(created_at)"))
            ->pluck('total', 'day')
            ->toArray();

        $chartData = [];
        for ($d = 1; $d <= 31; $d++) {
            $chartData[] = (float) ($salesData[$d] ?? 0);
        }
        return $chartData;
    }

    private function getPurchaseDataByMonth($month, $BranchID)
    {
        $purchaseData = DB::table('purchase_invoice')
            ->whereMonth('created_at', $month)
            ->where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->select(
                DB::raw("DAY(created_at) as day"),
                DB::raw("SUM(grand_total) as total")
            )
            ->groupBy(DB::raw("DAY(created_at)"))
            ->pluck('total', 'day')
            ->toArray();

        $chartData = [];
        for ($d = 1; $d <= 31; $d++) {
            $chartData[] = (float) ($purchaseData[$d] ?? 0);
        }
        return $chartData;
    }

    private function getSalesDataByYear($year, $BranchID)
    {
        $salesData = DB::table('orders')
            ->whereYear('created_at', $year)
            ->where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->select(
                DB::raw("MONTH(created_at) as month"),
                DB::raw("SUM(total_amount) as total")
            )
            ->groupBy(DB::raw("MONTH(created_at)"))
            ->pluck('total', 'month')
            ->toArray();

        $chart = [];
        for ($m = 1; $m <= 12; $m++) {
            $chart[] = (float) ($salesData[$m] ?? 0);
        }
        return $chart;
    }

    private function getPurchaseDataByYear($year, $BranchID)
    {
        $purchaseData = DB::table('purchase_invoice')
            ->whereYear('created_at', $year)
            ->where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->select(
                DB::raw("MONTH(created_at) as month"),
                DB::raw("SUM(grand_total) as total")
            )
            ->groupBy(DB::raw("MONTH(created_at)"))
            ->pluck('total', 'month')
            ->toArray();

        $chart = [];
        for ($m = 1; $m <= 12; $m++) {
            $chart[] = (float) ($purchaseData[$m] ?? 0);
        }
        return $chart;
    }

    public function profile()
    {
        $user = auth()->user();
        // Generate a fresh API token for staff so the production modal
        // can call the API without relying on localStorage timing.
        $apiToken = null;
        if ($user && $user->role === 'staff') {
            $apiToken = $user->createToken('StaffProfileToken')->accessToken;
        }
        return view('profile', compact('user', 'apiToken'));
    }
    public function taxrates()
    {

        return view('taxrates');
    }
    public function currency()
    {
        return view('currency');
    }
    public function ajaxSearch(Request $request)
    {
        $query = $request->get('query');

        $settings         = DB::table('settings')->first();
        $currencySymbol   = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';

        // Search users with role customer or vendor
        $users = User::where('name', 'LIKE', "%{$query}%")
            ->whereIn('role', ['customer', 'vendor'])
            ->take(10)
            ->get(['id', 'name', 'email', 'profile_image', 'role']);

        // Search products by name, include category_id, gst_option, product_gst
        $products = Product::where('name', 'LIKE', "%{$query}%")
            ->take(10)
            ->get(['id', 'name', 'price', 'images', 'category_id', 'gst_option', 'product_gst']);

        // Search orders by order number or customer name
        $orders = Order::with('user:id,name')
            ->where('order_number', 'LIKE', "%{$query}%")
            ->orWhereHas('user', function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->take(10)
            ->get(['id', 'order_number', 'user_id', 'total_amount', 'payment_status']);

        // Search purchase invoices
        $purchaseInvoices = PurchaseInvoice::where('invoice_number', 'LIKE', "%{$query}%")
            ->take(10)
            ->get(['id', 'invoice_number', 'grand_total']);

        // Helper to format amount
        $formatCurrency = function ($amount) use ($currencySymbol, $currencyPosition) {
            return $currencyPosition === 'left'
                ? $currencySymbol . number_format($amount, 2)
                : number_format($amount, 2) . $currencySymbol;
        };

        $results = [
            'users'             => $users->map(function ($user) {
                return [
                    'id'            => $user->id,
                    'name'          => $user->name,
                    'email'         => $user->email,
                    'profile_image' => $user->profile_image
                        ? asset(env('ImagePath') . 'storage/' . $user->profile_image)
                        : asset(env('ImagePath') . 'admin/assets/img/customer/default.jpg'),
                    'role'          => $user->role,
                ];
            }),
            'products'          => $products->map(function ($product) use ($formatCurrency) {
                $imageFileName = null;
                if (! empty($product->images)) {
                    $imagesArray = json_decode($product->images, true);
                    if (is_array($imagesArray) && count($imagesArray) > 0) {
                        $imageFileName = $imagesArray[0];
                    }
                }
                return [
                    'id'          => $product->id,
                    'name'        => $product->name,
                    'price'       => $formatCurrency($product->price), // formatted price
                    'category_id' => $product->category_id,
                    'gst_option'  => $product->gst_option,
                    'product_gst' => $product->product_gst,
                    'image'       => $imageFileName ? asset(env('ImagePath') . 'storage/' . $imageFileName) : asset(env('ImagePath') . 'admin/assets/img/product/noimage.png'),
                ];
            }),
            'orders'            => $orders->map(function ($order) use ($formatCurrency) {
                return [
                    'id'             => $order->id,
                    'order_number'   => $order->order_number,
                    'total_amount'   => $formatCurrency($order->total_amount), // formatted total
                    'payment_status' => $order->payment_status,
                    'user_name'      => $order->user ? $order->user->name : 'N/A',
                ];
            }),
            'purchase_invoices' => $purchaseInvoices->map(function ($invoice) use ($formatCurrency) {
                return [
                    'id'             => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'grand_total'    => $formatCurrency($invoice->grand_total),
                ];
            }),
        ];

        return response()->json($results);
    }

}
