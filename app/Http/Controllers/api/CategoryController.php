<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;

class CategoryController extends Controller
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
     | 🔹 Get All Categories
     -------------------------------------------------*/
    // public function getAllCategory(Request $request)
    // {
    //     $branchId = $this->resolveBranchId($request);

    //     $categories = Category::where('isDeleted', 0)
    //         ->where('branch_id', $branchId)
    //         ->latest('id')
    //         ->get();

    //     return response()->json([
    //         'status' => true,
    //         'data'   => $categories,
    //     ], 200);
    // }
    public function getAllCategory(Request $request)
    {
        $branchId = $this->resolveBranchId($request);

        // Pagination params
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);

        // Search keyword
        $search = $request->input('search');

        // Base query
        $query = Category::where('isDeleted', 0)
            ->where('branch_id', $branchId);

        // ✅ Apply search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%");
            });
        }

        // Pagination
        $categories = $query
            ->latest('id')
            ->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'status' => true,
            'data' => $categories->items(),
            'pagination' => [
                'current_page' => $categories->currentPage(),
                'last_page' => $categories->lastPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
                'next_page_url' => $categories->nextPageUrl(),
                'prev_page_url' => $categories->previousPageUrl(),
            ]
        ], 200);
    }

    /* -------------------------------------------------
     | 🔹 Create Category
     -------------------------------------------------*/
    public function addcategory(Request $request)
    {
        $branchId = $request->sub_admin_id ?? $this->resolveBranchId($request);

        $validated = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->where(function ($query) use ($branchId) {
                    return $query->where('branch_id', $branchId)->where('isDeleted', 0);
                }),
            ],
            // 'image'        => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp,gif',
                'mimetypes:image/jpeg,image/png,image/webp,image/gif',
                'max:2048' // 2MB limit
            ],
            'sub_admin_id' => 'nullable|numeric',
        ])->validate();

        // Image upload
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('img/category', 'public');
        }

        $category = Category::create([
            'name'      => $validated['name'],
            'image'     => $imagePath,
            'branch_id' => $validated['sub_admin_id'] ?? $branchId,
        ]);

        return response()->json([
            'status'   => true,
            'category' => $category,
        ], 200);
    }

    /* -------------------------------------------------
     | 🔹 Update Category
     -------------------------------------------------*/
    public function updatecategory(Request $request)
    {
        $category = Category::findOrFail($request->category_id);
        $branchId = $category->branch_id;

        $validated = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')
                    ->where(function ($query) use ($branchId) {
                        return $query->where('branch_id', $branchId)->where('isDeleted', 0);
                    })
                    ->ignore($request->category_id),
            ],
            // 'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp',
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp,gif',
                'mimetypes:image/jpeg,image/png,image/webp,image/gif',
                'max:2048' // 2MB limit
            ],
        ])->validate();

        // Image update
        if ($request->hasFile('image')) {
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            $category->image = $request->file('image')->store('img/category', 'public');
        }

        $category->update([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'status'   => true,
            'category' => $category->fresh(),
        ], 200);
    }

    /* -------------------------------------------------
     | 🔹 Delete Category (Safe)
     -------------------------------------------------*/
    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);

        $hasProducts = Product::where('category_id', $id)
            ->where('isDeleted', 0)
            ->exists();

        if ($hasProducts) {
            return response()->json([
                'status'  => false,
                'message' => 'This category is associated with products and cannot be deleted.',
            ], 400);
        }

        $category->update(['isDeleted' => 1]);

        return response()->json([
            'status'  => true,
            'message' => 'Category deleted successfully',
        ], 200);
    }
}
