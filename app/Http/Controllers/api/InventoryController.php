<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\OrderItem;
use App\Models\Purchases;
use App\Models\Brand;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Milon\Barcode\DNS1D;

class InventoryController extends Controller
{
    // public function index(Request $request)
    // {
    //     $user = Auth::guard('api')->user();
    //     $userId = $user->id;
    //     $userRole = $user->role;
    //     $userBranchId = $user->branch_id;

    //     $categoryId = $request->query('category_id');
    //     $brandId = $request->query('brand_id');
    //     $subBranchId = $request->sub_branch_id ?? $userId;

    //     // ✅ OPTIMIZED: Select only needed fields and eager load relationships
    //     $query = Product::with([
    //         'category:id,name',
    //         'brand:id,name',
    //         'product_inventory:id,product_id,initial_stock,current_stock'
    //     ])
    //         ->select('id', 'name', 'SKU', 'price', 'quantity', 'created_at', 'category_id', 'brand_id')
    //         ->where('isDeleted', 0)
    //         ->orderBy('id', 'desc');

    //     // ✅ Branch filter
    //     if ($userRole === 'admin') {
    //         if (!empty($subBranchId)) {
    //             $query->where('branch_id', $subBranchId);
    //         }
    //     } elseif ($userRole === 'sub-admin') {
    //         $query->where('branch_id', $userId);
    //     } elseif ($userRole === 'staff') {
    //         $query->where('branch_id', $userBranchId);
    //     }

    //     // ✅ Category filter
    //     if (!empty($categoryId)) {
    //         $query->where('category_id', $categoryId);
    //     }

    //     // ✅ Brand filter
    //     if (!empty($brandId)) {
    //         $query->where('brand_id', $brandId);
    //     }

    //     $products = $query->get();

    //     if ($products->isEmpty()) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'No products found.'
    //         ]);
    //     }

    //     // ✅ OPTIMIZED: Use collection map for better performance
    //     $data = $products->map(function ($product) {
    //         $inventory = $product->product_inventory;
    //         return [
    //             'id' => $product->id,
    //             'product_name' => $product->name,
    //             'sku' => $product->SKU,
    //             'initial_stock' => $inventory->initial_stock ?? 0,
    //             'current_stock' => $product->quantity,
    //             'price' => $product->price,
    //             'created_at' => $product->created_at->format('Y-m-d'),
    //         ];
    //     })->toArray();

    //     return response()->json([
    //         'status' => true,
    //         'inventory' => $data
    //     ]);
    // }
    public function index(Request $request)
    {
        $user = Auth::guard('api')->user();
        $userId = $user->id;
        $userRole = $user->role;
        $userBranchId = $user->branch_id;

        $categoryId = $request->query('category_id');
        $brandId = $request->query('brand_id');
        $subBranchId = $request->sub_branch_id ?? $userId;
        $search = $request->query('search', '');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);

        // Base query with necessary relationships
        $query = Product::with([
            'category:id,name',
            'brand:id,name',
            'product_inventory:id,product_id,initial_stock,current_stock'
        ])
            ->select('id', 'name', 'SKU', 'price', 'quantity', 'created_at', 'category_id', 'brand_id', 'branch_id')
            ->where('isDeleted', 0)
            ->orderBy('id', 'desc');

        // Branch filter
        if ($userRole === 'admin') {
            if (!empty($subBranchId)) {
                $query->where('branch_id', $subBranchId);
            }
        } elseif ($userRole === 'sub-admin') {
            $query->where('branch_id', $userId);
        } elseif ($userRole === 'staff') {
            $query->where('branch_id', $userBranchId);
        }

        // Category filter
        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        // Brand filter
        if (!empty($brandId)) {
            $query->where('brand_id', $brandId);
        }

        // Date range filter
        if (!empty($startDate)) {
            $query->whereDate('created_at', '>=', $startDate);
        }
        if (!empty($endDate)) {
            $query->whereDate('created_at', '<=', $endDate);
        }

        // Search filter (search in name or SKU)
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('SKU', 'like', "%{$search}%");
            });
        }

        // Paginate
        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        // Transform data
        $data = $paginated->map(function ($product) {
            $inventory = $product->product_inventory;
            return [
                'id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->SKU,
                'initial_stock' => $inventory->initial_stock ?? 0,
                'current_stock' => $product->quantity,
                'price' => $product->price,
                'created_at' => $product->created_at->format('Y-m-d'),
            ];
        });

        // Low stock threshold (optional)
        $lowStockThreshold = 5; // you can fetch from settings

        return response()->json([
            'status' => true,
            'inventory' => $data,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'from'         => $paginated->firstItem(),
                'to'           => $paginated->lastItem(),
            ],
            'lowStockThreshold' => $lowStockThreshold
        ]);
    }

    public function filter(Request $request)
    {
        $user = Auth::guard('api')->user();
        $userId = $user->id;
        $userRole = $user->role;
        $userBranchId = $user->branch_id;

        $categoryId   = $request->query('category_id');
        $brandId      = $request->query('brand_id');
        $subBranchId  = $request->sub_branch_id ?? $userId;
        $startDate    = $request->query('start_date');
        $endDate      = $request->query('end_date');

        // ✅ OPTIMIZED: Select only needed fields and eager load relationships
        $query = Product::with([
            'category:id,name',
            'brand:id,name',
            'product_inventory:id,product_id,initial_stock,current_stock'
        ])
            ->select('id', 'name', 'SKU', 'price', 'quantity', 'created_at', 'category_id', 'brand_id')
            ->where('isDeleted', 0)
            ->orderBy('id', 'desc');

        // ✅ Branch filter
        if ($userRole === 'admin') {
            if (!empty($subBranchId)) {
                $query->where('branch_id', $subBranchId);
            }
        } elseif ($userRole === 'sub-admin') {
            $query->where('branch_id', $userId);
        } elseif ($userRole === 'staff') {
            $query->where('branch_id', $userBranchId);
        }

        // ✅ Category filter
        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        // ✅ Brand filter
        if (!empty($brandId)) {
            $query->where('brand_id', $brandId);
        }

        // ✅ Date filter
        if (!empty($startDate) && !empty($endDate)) {
            $query->whereBetween('created_at', [
                $startDate . " 00:00:00",
                $endDate . " 23:59:59"
            ]);
        }

        $products = $query->get();
        // dd($products);

        if ($products->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'No products found.'
            ]);
        }

        // ✅ OPTIMIZED: Use collection map for better performance
        $data = $products->map(function ($product) {
            $inventory = $product->product_inventory;
            return [
                'id'            => $product->id,
                'product_name'  => $product->name,
                'sku'           => $product->SKU,
                'initial_stock' => $inventory->initial_stock ?? 0,
                'current_stock' => $product->quantity,
                'price'         => $product->price,
                'created_at'    => $product->created_at->format('Y-m-d'),
            ];
        })->toArray();

        return response()->json([
            'status'    => true,
            'inventory' => $data
        ]);
    }
    public function products_edit_inventroy(Request $request, $id)
    {
        // ✅ OPTIMIZED: Select only needed fields
        $product = Product::with('product_inventory:id,product_id,initial_stock,current_stock')
            ->select('id', 'name', 'SKU', 'price', 'quantity')
            ->find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'product' => [
                'id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->SKU,
                'price' => $product->price,
                'initial_stock' => $product->product_inventory->initial_stock ?? 0,
                'current_stock' => $product->quantity ?? 0,
            ]
        ]);
    }
    public function inventory_update(Request $request, $id)
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

        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found'
            ], 404);
        }

        // ✅ Calculate new stock based on add/minus
        $currentStock = (int) $product->quantity; // take from DB (latest)
        $adjustment = (int) $request->stock_quantity;

        if ($request->stock_action === 'add') {
            $newStock = $currentStock + $adjustment;
        } elseif ($request->stock_action === 'minus') {
            $newStock = max(0, $currentStock - $adjustment); // prevent negative
        } else {
            $newStock = $currentStock; // fallback
        }

        // ✅ Update product table
        $product->update([
            'price' => $request->price,
            'quantity' => $newStock,
        ]);

        // ✅ Create inventory log
        ProductInventory::create([
            'product_id' => $id,
            'initial_stock' => $request->initial_stock,
            'current_stock' => $newStock,
            'branch_id' => $userBranchId,
            'type' => $request->stock_action === 'add' ? 'Stock Added' : 'Stock Minus',
            'create_by' => $user->id,
            'date' => now(),
            'quantity' => $adjustment, // optional: track how much was added/removed
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Stock updated successfully',
            'new_stock' => $newStock
        ]);
    }
    // public function getQuantityHistory_inventory($productId)
    // {
    //     // ✅ OPTIMIZED: Select only needed fields
    //     $product = Product::select('id', 'name')->find($productId);
    //     if (!$product) {
    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Product not found.'
    //         ]);
    //     }

    //     // ✅ OPTIMIZED: Select only needed fields
    //     $productInventories = ProductInventory::select(
    //         'id', 'product_id', 'type', 'initial_stock', 'current_stock',
    //         'create_by', 'branch_id', 'date', 'created_at'
    //     )
    //         ->where('product_id', $productId)
    //         ->orderBy('id', 'desc')
    //         ->get();

    //     // ✅ OPTIMIZED: Bulk load all users in ONE query instead of N+1
    //     $userIds = $productInventories->pluck('create_by')
    //         ->merge($productInventories->pluck('branch_id'))
    //         ->filter()
    //         ->unique()
    //         ->toArray();

    //     $users = !empty($userIds)
    //         ? User::select('id', 'name')
    //             ->whereIn('id', $userIds)
    //             ->get()
    //             ->keyBy('id')
    //         : collect();

    //     // ✅ OPTIMIZED: Use collection map for better performance
    //     $history = $productInventories->map(function ($inventory) use ($users) {
    //         $userId = $inventory->create_by ?? $inventory->branch_id;
    //         $user = $users->get($userId);

    //         $initial = $inventory->initial_stock ?? 0;
    //         $current = $inventory->current_stock ?? 0;
    //         $qty = (!empty($initial) && !empty($current)) ? abs($current - $initial) : 0;

    //         // Determine sign based on type
    //         $signedQty = match(strtolower($inventory->type ?? '')) {
    //             'sale' => "-" . $qty,
    //             'purchase', 'stock added' => "+" . $qty,
    //             default => ($current > $initial) ? "+" . $qty : "-" . $qty
    //         };

    //         return [
    //             'type'          => $inventory->type,
    //             'initial_stock' => $initial,
    //             'current_stock' => $current,
    //             'quantity'      => $signedQty,
    //             'name'          => $user->name ?? 'Unknown',
    //             'date'          => $inventory->date
    //                 ? \Carbon\Carbon::parse($inventory->date)->format('d-M-Y')
    //                 : ($inventory->created_at ? $inventory->created_at->format('d-M-Y') : 'N/A'),
    //         ];
    //     })->values()->toArray();


    //     return response()->json([
    //         'status' => true,
    //         'product' => $product->name,
    //         'history' => $history, // Already sorted by orderBy('id', 'desc') in query
    //     ]);
    // }
    public function getQuantityHistory_inventory(Request $request, $productId)
    {
        $product = Product::select('id', 'name')->find($productId);
        if (!$product) {
            return response()->json([
                'status' => false,
                'message' => 'Product not found.'
            ]);
        }

        $search = $request->query('search', '');
        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);


        $query = ProductInventory::from('product_inventory as pi')
            ->leftJoin('users as u', 'pi.create_by', '=', 'u.id')
            ->select(
                'pi.*',
                'u.name as user_name'
            )
            ->where('pi.product_id', $productId);

        /*
    |--------------------------------------------------------------------------
    | ✅ GLOBAL SEARCH
    |--------------------------------------------------------------------------
    */
        if (!empty($search)) {

            $query->where(function ($q) use ($search) {

                // Type search
                $q->where('pi.type', 'like', "%{$search}%")

                    // Current stock
                    ->orWhere('pi.current_stock', 'like', "%{$search}%")

                    // User name
                    ->orWhere('u.name', 'like', "%{$search}%")

                    // Date search
                    ->orWhereDate('pi.date', $search)

                    // Quantity search (calculated)
                    ->orWhereRaw("
                ABS(pi.current_stock - pi.initial_stock) LIKE ?
            ", ["%{$search}%"]);
            });
        }

        $query->orderBy('pi.id', 'desc');


        // Paginate
        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        // Bulk load users
        $userIds = $paginated->pluck('create_by')
            ->merge($paginated->pluck('branch_id'))
            ->filter()
            ->unique()
            ->toArray();

        $users = !empty($userIds)
            ? User::select('id', 'name')
            ->whereIn('id', $userIds)
            ->get()
            ->keyBy('id')
            : collect();

        $history = $paginated->map(function ($inventory) use ($users) {
            $userId = $inventory->create_by ?? $inventory->branch_id;
            $user = $users->get($userId);

            $initial = $inventory->initial_stock ?? 0;
            $current = $inventory->current_stock ?? 0;
            $qty = (!empty($initial) && !empty($current)) ? abs($current - $initial) : 0;

            // Determine sign based on type
            $signedQty = match (strtolower($inventory->type ?? '')) {
                'sale' => "-" . $qty,
                'purchase', 'stock added' => "+" . $qty,
                default => ($current > $initial) ? "+" . $qty : "-" . $qty
            };

            return [
                'type'          => $inventory->type,
                'initial_stock' => $initial,
                'current_stock' => $current,
                'quantity'      => $signedQty,
                'name'          => $user->name ?? 'Unknown',
                'date'          => $inventory->date
                    ? \Carbon\Carbon::parse($inventory->date)->format('d-M-Y')
                    : ($inventory->created_at ? $inventory->created_at->format('d-M-Y') : 'N/A'),
            ];
        })->values()->toArray();

        return response()->json([
            'status' => true,
            'product' => $product->name,
            'history' => $history,
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'from'         => $paginated->firstItem(),
                'to'           => $paginated->lastItem(),
            ]
        ]);
    }
}
