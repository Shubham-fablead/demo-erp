<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CustomInvoice;
use App\Models\LogAttendance;
use App\Models\Attendance;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use App\Models\{PurchaseInvoice, Order, Expense, Notification, Product};

class LoginController extends Controller
{

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'nullable|string',
            'email'    => 'nullable|string|email',
            'password' => 'required|string',
        ]);

        // Accept either 'email' or 'username' field
        $email = $request->input('email') ?: $request->input('username');

        if (!$email) {
            return response()->json(['status' => false, 'error' => 'Email or username is required.'], 422);
        }

        $key = 'login-attempts-' . $request->ip();
        $force = filter_var($request->input('force', false), FILTER_VALIDATE_BOOLEAN);
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'status' => false,
                'error' => 'Too many login attempts. Please try again after 10 minutes.'
            ], 429);
        }
        if (Auth::attempt(['email' => $email, 'password' => $request->password])) {
            RateLimiter::clear($key); // reset after success
            $user = Auth::user();
            $token = $user->createToken('LaravelPassportToken')->accessToken;

            // Get permissions
            $permissions = $user->permissions()->with('module')->get();
            $formattedPermissions = [];
            foreach ($permissions as $permission) {
                $moduleName = $permission->module->module ?? 'Unknown';
                $formattedPermissions[$moduleName] = [
                    'id' => $permission->id,
                    'user_id' => $permission->user_id,
                    'module_id' => $permission->module_id,
                    'view' => $permission->view,
                    'create' => $permission->add,
                    'update' => $permission->edit,
                    'delete' => $permission->delete,
                    'created_at' => $permission->created_at,
                ];
            }

            $redirect = route('auth.dashboard');

            // Keep notification logging optional so missing table/config won't block login.
            $this->createLoginNotification($user);


            if ($user->role === 'staff') {
                $today = now('Asia/Kolkata')->format('Y-m-d');
                $currentTime = now('Asia/Kolkata')->format('H:i:s');

                // 🔹 Fetch branch settings
                $settings = Setting::where('branch_id', $user->branch_id)->first();

                $openTime = $settings->open_time ?? '09:00:00';
                $graceTime = $settings->grace_period ?? null; // example: "14:54"

                // 🔸 Determine attendance status
                $status = 'P'; // default Present
                if ($graceTime && $currentTime > $graceTime) {
                    $status = 'H'; // Half Day if login after grace period time
                }

                // 🔹 Find today's attendance
                $attendance = Attendance::where('user_id', $user->id)
                    ->where('date', $today)
                    ->first();

                // Case 1: Already logged in (no checkout)
                if ($attendance && empty($attendance->check_out_time)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'You are already logged in today. Please logout before logging in again.'
                    ], 400);
                }

                // Case 2: Already checked in & out — show warning (popup)
                if ($attendance && !empty($attendance->check_in_time) && !empty($attendance->check_out_time) && !$force) {
                    return response()->json([
                        'status' => true,
                        'warning' => true,
                        'message' => 'You have already checked in and checked out once today. Do you still want to log in again?',
                        'user' => $user,
                        'token' => $token,
                        'redirect' => route('auth.profile'),
                        'permissions' => $formattedPermissions,
                        'showAppointments' => false,
                    ]);
                }

                // 🔹 Record attendance
                LogAttendance::create([
                    'user_id' => $user->id,
                    'check_date' => $today,
                    'check_in' => $currentTime,
                    'checkout_out' => null,
                    'branch_id' => $user->branch_id ?? '0',
                ]);

                if (!$attendance) {
                    Attendance::create([
                        'user_id' => $user->id,
                        'branch_id' => $user->branch_id ?? '0',
                        'date' => $today,
                        'check_in_time' => $currentTime,
                        'check_out_time' => null,
                        'status' => $status, // ✅ P or H depending on login time
                    ]);
                } else {
                    $attendance->update([
                        // 'check_in_time' => $currentTime,
                        'check_out_time' => null,
                        // 'status' => $status,
                    ]);
                }

                $redirect = route('auth.profile');
            }

            return response()->json([
                'status' => true,
                'token' => $token,
                'user' => $user,
                'redirect' => $redirect,
                'permissions' => $formattedPermissions,
                'showAppointments' => ($user->role !== 'staff'),
            ]);
        }
        RateLimiter::hit($key, 600);
        return response()->json(['status' => false, 'error' => 'Login Credential Wrong'], 401);
    }

    private function createLoginNotification($user): void
    {
        if (!Schema::hasTable('notifications')) {
            return;
        }

        try {
            if (in_array($user->role, ['admin', 'staff'])) {
                $roleName = ucfirst($user->role);

                $loginMessage = match($user->role) {
                    'admin' => "Admin {$user->name} has successfully logged in",
                    'staff' => "Staff {$user->name} has successfully logged in",
                    default => "User {$user->name} has successfully logged in"
                };

                Notification::create([
                    'user_id'   => $user->id,
                    'type'      => 'login',
                    'title'     => "{$roleName} Login Successful",
                    'message'   => $loginMessage . ' at ' . now('Asia/Kolkata')->format('h:i A') . ' on ' . now('Asia/Kolkata')->format('d M Y'),
                    'link'      => '/profile',
                    'is_read'   => 0,
                    'is_sound'  => 0,
                    'branch_id' => $user->branch_id ?? 1,
                ]);
            } elseif ($user->role === 'vendor') {
                Notification::create([
                    'user_id'   => 1,
                    'type'      => 'vendor_login',
                    'title'     => 'Vendor Login Alert',
                    'message'   => "Vendor {$user->name} has successfully logged in at " . now('Asia/Kolkata')->format('h:i A') . ' on ' . now('Asia/Kolkata')->format('d M Y'),
                    'link'      => '/profile',
                    'is_read'   => 0,
                    'is_sound'  => 0,
                    'branch_id' => $user->branch_id ?? 1,
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('Login notification skipped: ' . $e->getMessage());
        }
    }

    // Logout User
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user && $user->role === 'staff') {
            $today = now('Asia/Kolkata')->format('Y-m-d');
            $time = now('Asia/Kolkata')->format('H:i:s');

            // ✅ Update the last user_attendance record for this user (if not checked out yet)
            $lastAttendance = LogAttendance::where('user_id', $user->id)
                ->where('check_date', $today)
                ->whereNull('checkout_out')
                ->latest('id')
                ->first();

            if ($lastAttendance) {
                $lastAttendance->update([
                    'checkout_out' => $time,
                ]);
            }

            // ✅ Update today's main attendance record
            $attendance = Attendance::where('user_id', $user->id)
                ->where('date', $today)
                ->first();

            if ($attendance) {
                $attendance->update([
                    'check_out_time' => $time,
                ]);
            }
        }

        // ✅ Standard logout process
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    public function logoutapi(Request $request)
    {
        $user = $request->user('api');

        if ($user && $user->role === 'staff') {
            $today = now('Asia/Kolkata')->format('Y-m-d');
            $time = now('Asia/Kolkata')->format('H:i:s');

            // ✅ Update last login record in user_attendance
            $lastAttendance = LogAttendance::where('user_id', $user->id)
                ->where('check_date', $today)
                ->whereNull('checkout_out')
                ->latest('id')
                ->first();

            if ($lastAttendance) {
                $lastAttendance->update([
                    'checkout_out' => $time,
                ]);
            }

            // ✅ Update today's main attendance record
            $attendance = Attendance::where('user_id', $user->id)
                ->where('date', $today)
                ->first();

            if ($attendance) {
                $attendance->update([
                    'check_out_time' => $time,
                ]);
            }
        }

        // ✅ Revoke access token (Passport)
        $request->user('api')->token()->revoke();

        return response()->json([
            'status' => true,
            'message' => 'Logout Successfully',
        ], 200);
    }


    public function dashboardApi(Request $request)
    {
        $user = Auth::guard('api')->user();
        $role = $user->role;

        // Default branch is the logged-in user's branch
        $BranchID = $user->id;

        // Admin can override with selected sub-admin
        $selectedSubAdminId = $request->query('selectedSubAdminId');

        if ($role === 'admin' && !empty($selectedSubAdminId)) {
            $subAdmin = User::where('id', $selectedSubAdminId)
                ->where('role', 'sub-admin')
                ->first();

            if ($subAdmin) {
                $BranchID = $subAdmin->id; // ✅ use branch_id, not id
            }
        } elseif ($role === 'sub-admin') {
            // Sub-admin: only their branch
            $BranchID = $user->id;
        } elseif ($role === 'staff') {
            // Staff: only their branch
            $BranchID = $user->id;
        }
        //  dd($BranchID);
        // dd($selectedSubAdminId);

        $currentYear = Carbon::now()->year;
        $previousYear = $currentYear - 1;
        $currentMonth = Carbon::now()->month;

        // ✅ Charts
        $salesChartThisMonth = $this->getSalesDataByMonth($currentMonth, $BranchID);
        $purchaseChartThisMonth = $this->getPurchaseDataByMonth($currentMonth, $BranchID);

        $salesChartThisYear = $this->getSalesDataByYear($currentYear, $BranchID);
        $salesChartPreviousYear = $this->getSalesDataByYear($previousYear, $BranchID);

        $purchaseChartThisYear = $this->getPurchaseDataByYear($currentYear, $BranchID);
        $purchaseChartPreviousYear = $this->getPurchaseDataByYear($previousYear, $BranchID);

        // ✅ Totals
        $totalPurchaseAmount = PurchaseInvoice::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->sum('grand_total');

        $totalSalesAmount = Order::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->sum('total_amount');

        $totalExpenseAmount = Expense::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->sum('amount');


        $totalInvoiceAmount = CustomInvoice::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->sum('grand_total');

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

        // ✅ Recent Products
        $recentProducts = Product::where('isDeleted', '!=', 1)
            ->where('branch_id', $BranchID)
            ->latest()
            ->take(4)
            ->get();

        // ✅ Latest Sales
        $latestSales = DB::table('order_items')
            ->join('products', function ($join) {
                $join->on('order_items.product_id', '=', 'products.id')
                    ->where('products.isDeleted', '!=', 1);
            })
            ->leftJoin('brands', 'products.brand_id', '=', 'brands.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', function ($join) use ($BranchID) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->where('orders.isDeleted', '!=', 1)
                    ->where('orders.branch_id', '=', $BranchID);
            })
            ->where('order_items.isDeleted', '!=', 1)
            ->select(
                'order_items.*',
                'products.id as product_id',
                'products.SKU as product_code',
                'products.name as product_name',
                'products.images',
                'brands.name as brand_name',
                'categories.name as category_name',
                'orders.id as order_id',
                'orders.created_at as order_date',
                'orders.total_amount',
                'orders.payment_method',
                'orders.order_number'
            )
            ->orderBy('order_items.created_at', 'desc')
            ->limit(4)
            ->get();

        // ✅ Append image_url to each item
        $basePath = env('ImagePath', '/');

        $latestSales = $latestSales->map(function ($item) use ($basePath) {
            $decoded = json_decode($item->images, true);

            if (is_array($decoded)) {
                $item->image_url = array_map(function ($img) use ($basePath) {
                    return url($basePath . 'storage/' . $img);
                }, $decoded);
            } elseif ($item->images) {
                $item->image_url = [url($basePath . 'storage/' . $item->images)];
            } else {
                $item->image_url = [url($basePath . 'admin/assets/img/product/noimage.png')];
            }

            return $item;
        });

        // ✅ Latest Purchases
        $latestPurchases = DB::table('purchases as pur')
            ->leftJoin('products as pr', function ($join) {
                $join->on('pr.id', '=', 'pur.item')
                    ->where('pr.isDeleted', '!=', 1);
            })
            ->leftJoin('purchase_invoice as pi', 'pi.id', '=', 'pur.invoice_id')
            ->leftJoin('brands as br', 'pr.brand_id', '=', 'br.id')
            ->leftJoin('categories as cat', 'pr.category_id', '=', 'cat.id')
            ->where('pur.isDeleted', '!=', 1)
            ->where('pi.isDeleted', '!=', 1)
            ->where('pi.branch_id', '=', $BranchID)
            ->select(
                'pi.id as invoice_id',
                'pur.created_at as purchase_date',
                'pr.id as product_id',
                'pr.name as product_name',
                'pr.SKU as product_code',
                'pr.images',
                'cat.name as category_name',
                'br.name as brand_name',
                'pur.amount_total',
                'pi.invoice_number',
                'pi.grand_total'
            )
            ->orderBy('pur.created_at', 'desc')
            ->limit(4)
            ->get();

        // ✅ Append image_url to each purchase record
        $basePath = env('ImagePath', '/');

        $latestPurchases = $latestPurchases->map(function ($item) use ($basePath) {
            $decoded = json_decode($item->images, true);

            if (is_array($decoded)) {
                $item->image_url = array_map(function ($img) use ($basePath) {
                    return url($basePath . 'storage/' . $img);
                }, $decoded);
            } elseif ($item->images) {
                $item->image_url = [url($basePath . 'storage/' . $item->images)];
            } else {
                $item->image_url = [url($basePath . 'admin/assets/img/product/noimage.png')];
            }

            return $item;
        });
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

        $salesChart = [];
        $purchaseChart = [];
        for ($m = 1; $m <= 12; $m++) {
            $salesChart[] = (float) ($salesData[$m] ?? 0);
            $purchaseChart[] = (float) ($purchasesData[$m] ?? 0);
        }

        // ✅ Settings
        $settings = DB::table('settings')->where('branch_id', $BranchID)->first();
        $currencySymbol = $settings->currency_symbol ?? '₹';
        $currencyPosition = $settings->currency_position ?? 'left';
        $lowStockThreshold = (float) ($settings->low_stock ?? 0);

        // ✅ Low Stock Products (only when threshold is set)
        $lowStockProducts = [];
        if ($lowStockThreshold > 0) {
            $lowStockProducts = Product::where('isDeleted', '!=', 1)
                ->where('branch_id', $BranchID)
                ->where('quantity', '<', $lowStockThreshold)
                ->orderBy('quantity', 'asc')
                ->get(['id', 'name', 'quantity', 'availablility'])
                ->toArray();
        }

        return response()->json([
            'status' => true,
            'branch_id' => $BranchID,
            'data' => [
                'totals' => [
                    'purchase' => $totalPurchaseAmount,
                    'sales' => $totalSalesAmount,
                    'expense' => $totalExpenseAmount,
                ],
                'counts' => [
                    'customers' => $customerCount,
                    'vendors' => $vendorCount,
                    'purchaseInvoices' => $purchaseInvoiceCount,
                    'salesInvoices' => $salesInvoiceCount,
                ],
                'recentProducts' => $recentProducts,
                'latestSales' => $latestSales,
                'latestPurchases' => $latestPurchases,
                'charts' => [
                    'sales' => $salesChart,
                    'purchases' => $purchaseChart,
                    'salesThisYear' => $salesChartThisYear,
                    'salesPreviousYear' => $salesChartPreviousYear,
                    'purchaseThisYear' => $purchaseChartThisYear,
                    'purchasePreviousYear' => $purchaseChartPreviousYear,
                    'salesThisMonth' => $salesChartThisMonth,
                    'purchaseThisMonth' => $purchaseChartThisMonth,
                ],
                'currency' => [
                    'symbol' => $currencySymbol,
                    'position' => $currencyPosition,
                ],
                'lowStock' => [
                    'threshold' => $lowStockThreshold,
                    'products' => $lowStockProducts,
                ],
            ]
        ], 200);
    }
    private function getSalesDataByMonth($month, $BranchID)
    {
        $salesData = DB::table('order_items')
            ->join('orders', function ($join) use ($BranchID) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->where('orders.isDeleted', '!=', 1)
                    ->where('orders.branch_id', '=', $BranchID);
            })
            ->whereMonth('orders.created_at', $month)
            ->where('order_items.isDeleted', '!=', 1)
            ->select(
                DB::raw("DAY(orders.created_at) as day"),
                DB::raw("SUM(order_items.total_amount) as total")
            )
            ->groupBy(DB::raw("DAY(orders.created_at)"))
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
        $purchaseData = DB::table('purchases')
            ->join('purchase_invoice', function ($join) use ($BranchID) {
                $join->on('purchase_invoice.id', '=', 'purchases.invoice_id')
                    ->where('purchase_invoice.isDeleted', '!=', 1)
                    ->where('purchase_invoice.branch_id', '=', $BranchID);
            })
            ->whereMonth('purchases.created_at', $month)
            ->where('purchases.isDeleted', '!=', 1)
            ->select(
                DB::raw("DAY(purchases.created_at) as day"),
                DB::raw("SUM(purchases.amount_total) as total")
            )
            ->groupBy(DB::raw("DAY(purchases.created_at)"))
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
        $salesData = DB::table('order_items')
            ->join('orders', function ($join) use ($BranchID) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->where('orders.isDeleted', '!=', 1)
                    ->where('orders.branch_id', '=', $BranchID);
            })
            ->whereYear('orders.created_at', $year)
            ->where('order_items.isDeleted', '!=', 1)
            ->select(
                DB::raw("MONTH(orders.created_at) as month"),
                DB::raw("SUM(order_items.total_amount) as total")
            )
            ->groupBy(DB::raw("MONTH(orders.created_at)"))
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
        $purchaseData = DB::table('purchases')
            ->join('purchase_invoice', function ($join) use ($BranchID) {
                $join->on('purchase_invoice.id', '=', 'purchases.invoice_id')
                    ->where('purchase_invoice.isDeleted', '!=', 1)
                    ->where('purchase_invoice.branch_id', '=', $BranchID);
            })
            ->whereYear('purchases.created_at', $year)
            ->where('purchases.isDeleted', '!=', 1)
            ->select(
                DB::raw("MONTH(purchases.created_at) as month"),
                DB::raw("SUM(purchases.amount_total) as total")
            )
            ->groupBy(DB::raw("MONTH(purchases.created_at)"))
            ->pluck('total', 'month')
            ->toArray();

        $chart = [];
        for ($m = 1; $m <= 12; $m++) {
            $chart[] = (float) ($purchaseData[$m] ?? 0);
        }
        return $chart;
    }
}
