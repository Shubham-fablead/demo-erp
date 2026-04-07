<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Brand;
use App\Models\Product;

class BrandController extends Controller
{
    /* -------------------------------------------------
     | 🔹 Helper: Resolve Branch ID
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
     | 🔹 Get All Brands
     -------------------------------------------------*/
    // public function getAllBrand(Request $request)
    // {
    //     $branchId = $this->resolveBranchId($request);

    //     $brands = Brand::where('isDeleted', 0)
    //         ->where('branch_id', $branchId)
    //         ->latest('id')
    //         ->get();

    //     return response()->json([
    //         'status' => true,
    //         'data'   => $brands,
    //     ], 200);
    // }

    public function getAllBrand(Request $request)
{
    $branchId = $this->resolveBranchId($request);

    $perPage = $request->input('per_page', 10);
    $page = $request->input('page', 1);
    $search = $request->input('search');

    $query = Brand::where('isDeleted', 0)
        ->where('branch_id', $branchId);

    if (!empty($search)) {
        $query->where('name', 'LIKE', "%{$search}%");
    }

    $brands = $query->latest('id')->paginate($perPage, ['*'], 'page', $page);

    return response()->json([
        'status' => true,
        'data' => $brands->items(),
        'pagination' => [
            'current_page' => $brands->currentPage(),
            'last_page' => $brands->lastPage(),
            'per_page' => $brands->perPage(),
            'total' => $brands->total(),
            'next_page_url' => $brands->nextPageUrl(),
            'prev_page_url' => $brands->previousPageUrl(),
        ]
    ], 200);
}

    /* -------------------------------------------------
     | 🔹 Create Brand
     -------------------------------------------------*/
    public function addBrand(Request $request)
    {
        $branchId = $request->sub_admin_id ?? $this->resolveBranchId($request);
        $validated = Validator::make($request->all(), [
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('brands')
                    ->where('branch_id', $branchId)
                    ->where('isDeleted', 0),
            ],
            'logo' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp,gif',
                'mimetypes:image/jpeg,image/png,image/webp,image/gif',
                'max:2048'
            ],
            'sub_admin_id' => 'nullable|numeric',
        ])->validate();

        $branchId = $validated['sub_admin_id']
            ?? $this->resolveBranchId($request);

        // Upload logo
        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('img/brand', 'public');
        }

        $brand = Brand::create([
            'name'      => $validated['name'],
            'logo'      => $logoPath,
            'branch_id' => $branchId,
        ]);

        return response()->json([
            'status' => true,
            'brand'  => $brand,
        ], 200);
    }

    /* -------------------------------------------------
     | 🔹 Update Brand
     -------------------------------------------------*/
    public function updateBrand(Request $request)
    {

        $brand = Brand::findOrFail($request->brand_id);
        $branchId = $brand->branch_id;

        $validated = Validator::make($request->all(), [
            'brand_id' => 'required|exists:brands,id',
            'name' => [
                'required',
                'string',
                'max:255',
                \Illuminate\Validation\Rule::unique('brands')
                    ->ignore($brand->id)
                    ->where(function ($query) use ($branchId) {
                        return $query
                            ->where('branch_id', $branchId)
                            ->where('isDeleted', 0);
                    }),
            ],
            'image' => [
                'nullable',
                'image',
                'mimes:jpeg,jpg,png,webp,gif',
                'mimetypes:image/jpeg,image/png,image/webp,image/gif',
                'max:2048'
            ],
        ])->validate();

        // Update logo if new uploaded
        if ($request->hasFile('image')) {
            if ($brand->logo && Storage::disk('public')->exists($brand->logo)) {
                Storage::disk('public')->delete($brand->logo);
            }

            $brand->logo = $request->file('image')->store('img/brand', 'public');
        }

        $brand->update([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'status' => true,
            'brand'  => $brand->fresh(),
        ], 200);
    }

    /* -------------------------------------------------
     | 🔹 Delete Brand (Safe)
     -------------------------------------------------*/
    public function deleteBrand($id)
    {
        $brand = Brand::findOrFail($id);

        $isUsed = Product::where('brand_id', $id)
            ->where('isDeleted', 0)
            ->exists();

        if ($isUsed) {
            return response()->json([
                'status'  => false,
                'message' => 'This brand is associated with products and cannot be deleted.',
            ], 409);
        }

        $brand->update(['isDeleted' => 1]);

        return response()->json([
            'status'  => true,
            'message' => 'Brand deleted successfully.',
        ], 200);
    }
}
