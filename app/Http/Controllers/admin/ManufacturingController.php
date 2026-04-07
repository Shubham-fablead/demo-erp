<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\RowMaterial;
use Illuminate\Http\Request;

class ManufacturingController extends Controller
{
    private function resolveBranchId()
    {
        $user = auth()->user();
        $role = strtolower($user->role ?? '');
        $subAdminId = session('selectedSubAdminId');

        return match ($role) {
            'sub-admin' => $user->id,
            'staff' => $user->branch_id,
            'admin' => $subAdminId ?: $user->id,
            default => $user->id,
        };
    }

    public function bomIndex()
    {
        return view('manufacturing.bom-list');
    }

    public function bomCreate()
    {
        $branchId = $this->resolveBranchId();
        $products = Product::with('unit')
            ->where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->orderBy('name')
            ->get();

        $materials = RowMaterial::with('unit')
            ->where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->orderBy('row_materialname')
            ->get();

        return view('manufacturing.bom-create', compact('products', 'materials'));
    }

    public function productionIndex()
    {
        return view('manufacturing.production-list');
    }

    public function productionCreate()
    {
        return view('manufacturing.production-create');
    }
}
