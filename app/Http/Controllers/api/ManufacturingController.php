<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Bom;
use App\Models\BomItem;
use App\Models\Product;
use App\Models\ProductInventory;
use App\Models\Production;
use App\Models\ProductionItem;
use App\Models\RowMaterial;
use App\Models\RowMaterialInventory;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ManufacturingController extends Controller
{
    private function validateBomRequest(Request $request)
    {
        return Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'base_quantity' => 'required|numeric|gt:0',
            'notes' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'items' => 'required|array|min:1',
            'items.*.raw_material_id' => 'required|integer|exists:row_material,id',
            'items.*.qty' => 'required|numeric|gt:0',
            'items.*.unit_id' => 'nullable|integer|exists:units,id',
            'items.*.notes' => 'nullable|string',
        ]);
    }

    private function resolveBranchId(Request $request): ?int
    {
        $user = Auth::guard('api')->user();

        return match (strtolower($user->role ?? '')) {
            'sub-admin' => $user->id,
            'staff' => $user->branch_id,
            'admin' => $request->selectedSubAdminId ?: $user->id,
            default => $user->id,
        };
    }

    private function generateCode(string $prefix, string $table, string $column): string
    {
        do {
            $value = $prefix . '-' . mt_rand(100000, 999999);
        } while (DB::table($table)->where($column, $value)->exists());

        return $value;
    }

    private function buildProductionPreview(Bom $bom, float $productionQty, ?float $wastageOverride = null): array
    {
        $product = $bom->product;
        $baseQuantity = (float) ($bom->base_quantity ?: 1);
        $factor = $baseQuantity > 0 ? ($productionQty / $baseQuantity) : 0;
        // Use production-level wastage if provided, otherwise fall back to BOM wastage
        $wastagePercentage = $wastageOverride !== null ? $wastageOverride : (float) ($bom->wastage_percentage ?? 0);
        $wastageQty = round(($productionQty * $wastagePercentage) / 100, 3);
        $outputQty = max(round($productionQty - $wastageQty, 3), 0);

        $materials = [];
        $materialCost = 0;
        $insufficientItems = [];

        foreach ($bom->items as $item) {
            $rawMaterial = $item->rawMaterial;
            if (! $rawMaterial) {
                continue;
            }

            $requiredQty = round((float) $item->qty * $factor, 3);
            $availableStock = round((float) ($rawMaterial->quantity ?? 0), 3);
            $rate = round((float) ($rawMaterial->price ?? 0), 2);
            $lineCost = round($requiredQty * $rate, 2);

            if ($availableStock < $requiredQty) {
                $insufficientItems[] = $rawMaterial->row_materialname;
            }

            $materials[] = [
                'raw_material_id' => $rawMaterial->id,
                'name' => $rawMaterial->row_materialname,
                'unit_name' => optional($rawMaterial->unit)->unit_name ?? optional($item->unit)->unit_name,
                'required_qty' => $requiredQty,
                'consume_qty' => $requiredQty,
                'available_stock' => $availableStock,
                'rate' => $rate,
                'total_cost' => $lineCost,
                'has_stock' => $availableStock >= $requiredQty,
            ];

            $materialCost += $lineCost;
        }

        return [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
                'unit_name' => optional($product->unit)->unit_name,
            ],
            'bom' => [
                'id' => $bom->id,
                'bom_code' => $bom->bom_code,
                'base_quantity' => $baseQuantity,
                'wastage_percentage' => $wastagePercentage,
            ],
            'production_qty' => round($productionQty, 3),
            'wastage_qty' => $wastageQty,
            'output_qty' => $outputQty,
            'material_cost' => round($materialCost, 2),
            'items' => $materials,
            'insufficient_items' => $insufficientItems,
        ];
    }

    public function productOptions(Request $request)
    {
        $branchId = $this->resolveBranchId($request);

        $products = Product::with(['unit:id,unit_name'])
            ->where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->orderBy('name')
            ->get(['id', 'name', 'unit_id', 'branch_id']);

        return response()->json([
            'status' => true,
            'data' => $products,
        ]);
    }

    public function materialOptions(Request $request)
    {
        $branchId = $this->resolveBranchId($request);

        $materials = RowMaterial::with(['unit:id,unit_name'])
            ->where('isDeleted', 0)
            ->where('branch_id', $branchId)
            ->orderBy('row_materialname')
            ->get(['id', 'row_materialname', 'unit_id', 'price', 'quantity', 'branch_id']);

        return response()->json([
            'status' => true,
            'data' => $materials,
        ]);
    }

    public function bomList(Request $request)
    {
        $branchId = $this->resolveBranchId($request);

        $boms = Bom::with(['product:id,name,unit_id', 'product.unit:id,unit_name'])
            ->withCount('items')
            ->where('branch_id', $branchId)
            ->latest('id')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $boms,
        ]);
    }

    public function bomDetails(Request $request, $id)
    {
        $branchId = $this->resolveBranchId($request);
        $bom = Bom::with([
            'product:id,name,unit_id',
            'product.unit:id,unit_name',
            'items.rawMaterial:id,row_materialname,unit_id,price,quantity',
            'items.rawMaterial.unit:id,unit_name',
            'items.unit:id,unit_name',
        ])
            ->where('branch_id', $branchId)
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $bom,
        ]);
    }

    public function storeBom(Request $request)
    {
        $branchId = $this->resolveBranchId($request);
        $user = Auth::guard('api')->user();

        $validator = $this->validateBomRequest($request);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $bom = Bom::create([
                'bom_code' => $this->generateCode('BOM', 'boms', 'bom_code'),
                'product_id' => $request->product_id,
                'base_quantity' => $request->base_quantity,
                'wastage_percentage' => 0,
                'notes' => $request->notes,
                'status' => $request->status,
                'branch_id' => $branchId,
                'created_by' => $user->id,
            ]);

            foreach ($request->items as $item) {
                $material = RowMaterial::with('unit')->findOrFail($item['raw_material_id']);

                BomItem::create([
                    'bom_id' => $bom->id,
                    'raw_material_id' => $item['raw_material_id'],
                    'qty' => $item['qty'],
                    'unit_id' => $item['unit_id'] ?? $material->unit_id,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            if ($request->status === 'active') {
                Bom::where('product_id', $request->product_id)
                    ->where('branch_id', $branchId)
                    ->where('id', '!=', $bom->id)
                    ->update(['status' => 'inactive']);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'BOM saved successfully.',
                'data' => $bom->load('items'),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateBom(Request $request, $id)
    {
        $branchId = $this->resolveBranchId($request);
        $validator = $this->validateBomRequest($request);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $bom = Bom::where('branch_id', $branchId)->findOrFail($id);

            $bom->update([
                'product_id' => $request->product_id,
                'base_quantity' => $request->base_quantity,
                'wastage_percentage' => 0,
                'notes' => $request->notes,
                'status' => $request->status,
            ]);

            BomItem::where('bom_id', $bom->id)->delete();

            foreach ($request->items as $item) {
                $material = RowMaterial::with('unit')->findOrFail($item['raw_material_id']);

                BomItem::create([
                    'bom_id' => $bom->id,
                    'raw_material_id' => $item['raw_material_id'],
                    'qty' => $item['qty'],
                    'unit_id' => $item['unit_id'] ?? $material->unit_id,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            if ($request->status === 'active') {
                Bom::where('product_id', $request->product_id)
                    ->where('branch_id', $branchId)
                    ->where('id', '!=', $bom->id)
                    ->update(['status' => 'inactive']);
            }

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'BOM updated successfully.',
                'data' => $bom->load([
                    'product:id,name,unit_id',
                    'product.unit:id,unit_name',
                    'items.rawMaterial:id,row_materialname,unit_id,price,quantity',
                    'items.rawMaterial.unit:id,unit_name',
                    'items.unit:id,unit_name',
                ]),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function bomForProduct(Request $request, $productId)
    {
        $branchId = $this->resolveBranchId($request);

        $bom = Bom::with([
            'product:id,name,unit_id',
            'product.unit:id,unit_name',
            'items.rawMaterial:id,row_materialname,unit_id,price,quantity',
            'items.rawMaterial.unit:id,unit_name',
            'items.unit:id,unit_name',
        ])
            ->where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->where('status', 'active')
            ->first();

        if (! $bom) {
            return response()->json([
                'status' => false,
                'message' => 'No active BOM found for the selected product.',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $bom,
        ]);
    }

    public function deleteBom(Request $request, $id)
    {
        $branchId = $this->resolveBranchId($request);

        $bom = Bom::where('branch_id', $branchId)->findOrFail($id);

        if (Production::where('branch_id', $branchId)->where('bom_id', $bom->id)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'This BOM is already used in production and cannot be deleted.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            BomItem::where('bom_id', $bom->id)->delete();
            $bom->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'BOM deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function productionPreview(Request $request)
    {
        $branchId = $this->resolveBranchId($request);

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'production_qty' => 'required|numeric|gt:0',
            'wastage_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $bom = Bom::with([
            'product:id,name,unit_id',
            'product.unit:id,unit_name',
            'items.rawMaterial:id,row_materialname,unit_id,price,quantity',
            'items.rawMaterial.unit:id,unit_name',
            'items.unit:id,unit_name',
        ])
            ->where('branch_id', $branchId)
            ->where('product_id', $request->product_id)
            ->where('status', 'active')
            ->first();

        if (! $bom) {
            return response()->json([
                'status' => false,
                'message' => 'No active BOM found for the selected product.',
            ], 404);
        }

        $wastageOverride = $request->has('wastage_percentage') ? (float) $request->wastage_percentage : null;
        $preview = $this->buildProductionPreview($bom, (float) $request->production_qty, $wastageOverride);

        return response()->json([
            'status' => true,
            'data' => $preview,
        ]);
    }

    public function productionList(Request $request)
    {
        $branchId = $this->resolveBranchId($request);

        $productions = Production::with(['product:id,name,unit_id', 'product.unit:id,unit_name', 'bom:id,bom_code'])
            ->where('branch_id', $branchId)
            ->latest('id')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $productions,
        ]);
    }

    public function productionDetails(Request $request, $id)
    {
        $branchId = $this->resolveBranchId($request);

        $production = Production::with([
            'product:id,name,unit_id',
            'product.unit:id,unit_name',
            'bom:id,bom_code,base_quantity,wastage_percentage,notes',
            'items.rawMaterial:id,row_materialname,unit_id,price',
            'items.rawMaterial.unit:id,unit_name',
        ])
            ->where('branch_id', $branchId)
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data' => $production,
        ]);
    }

    public function storeProduction(Request $request)
    {
        $branchId = $this->resolveBranchId($request);
        $user = Auth::guard('api')->user();

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'production_qty' => 'required|numeric|gt:0',
            'production_date' => 'required|date',
            'status' => 'required|in:draft,in_production,completed',
            'wastage_percentage' => 'nullable|numeric|min:0|max:100',
            'labor_cost' => 'nullable|numeric|min:0',
            'electricity_cost' => 'nullable|numeric|min:0',
            'extra_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'batch_no' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $bom = Bom::with([
            'product:id,name,unit_id,quantity,branch_id',
            'items.rawMaterial:id,row_materialname,unit_id,price,quantity,branch_id,availablility',
            'items.rawMaterial.unit:id,unit_name',
            'items.unit:id,unit_name',
        ])
            ->where('branch_id', $branchId)
            ->where('product_id', $request->product_id)
            ->where('status', 'active')
            ->first();

        if (! $bom) {
            return response()->json([
                'status' => false,
                'message' => 'No active BOM found for the selected product.',
            ], 404);
        }

        $wastageOverride = $request->has('wastage_percentage') ? (float) $request->wastage_percentage : null;
        $preview = $this->buildProductionPreview($bom, (float) $request->production_qty, $wastageOverride);
        $laborCost = round((float) ($request->labor_cost ?? 0), 2);
        $electricityCost = round((float) ($request->electricity_cost ?? 0), 2);
        $extraCost = round((float) ($request->extra_cost ?? 0), 2);
        $totalCost = round($preview['material_cost'] + $laborCost + $electricityCost + $extraCost, 2);
        $costPerUnit = $preview['output_qty'] > 0 ? round($totalCost / $preview['output_qty'], 4) : 0;

        // Both in_production and completed require sufficient raw material stock
        if (in_array($request->status, ['in_production', 'completed']) && ! empty($preview['insufficient_items'])) {
            return response()->json([
                'status' => false,
                'message' => 'Insufficient stock for: ' . implode(', ', $preview['insufficient_items']),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $production = Production::create([
                'production_no' => $this->generateCode('PROD', 'productions', 'production_no'),
                'product_id' => $request->product_id,
                'bom_id' => $bom->id,
                'production_qty' => $preview['production_qty'],
                'output_qty' => $preview['output_qty'],
                'wastage_qty' => $preview['wastage_qty'],
                'wastage_percentage' => $preview['bom']['wastage_percentage'],
                'extra_cost' => $extraCost,
                'labor_cost' => $laborCost,
                'electricity_cost' => $electricityCost,
                'total_cost' => $totalCost,
                'cost_per_unit' => $costPerUnit,
                'production_date' => Carbon::parse($request->production_date)->format('Y-m-d'),
                'status' => $request->status,
                'batch_no' => $request->batch_no,
                'expiry_date' => $request->expiry_date ? Carbon::parse($request->expiry_date)->format('Y-m-d') : null,
                'notes' => $request->notes,
                'branch_id' => $branchId,
                'created_by' => $user->id,
            ]);

            foreach ($preview['items'] as $item) {
                ProductionItem::create([
                    'production_id' => $production->id,
                    'raw_material_id' => $item['raw_material_id'],
                    'required_qty' => $item['required_qty'],
                    'consume_qty' => $item['consume_qty'],
                    'rate' => $item['rate'],
                    'total_cost' => $item['total_cost'],
                ]);
            }

            // in_production: deduct raw materials (production started, materials consumed)
            // completed (direct): deduct raw materials AND add finished product
            if (in_array($request->status, ['in_production', 'completed'])) {
                foreach ($preview['items'] as $item) {
                    $material = RowMaterial::findOrFail($item['raw_material_id']);
                    $previousStock = (float) $material->quantity;
                    $newStock = round($previousStock - $item['consume_qty'], 3);

                    $material->update([
                        'quantity' => $newStock,
                        'availablility' => $newStock > 0 ? 'in_stock' : 'out_stock',
                    ]);

                    RowMaterialInventory::create([
                        'row_material_id' => $material->id,
                        'initial_stock' => $previousStock,
                        'current_stock' => $newStock,
                        'branch_id' => $branchId,
                        'create_by' => $user->id,
                        'type' => $request->status === 'in_production' ? 'Production In-Progress' : 'Production Consume',
                        'date' => now(),
                    ]);
                }
            }

            // Only add finished product to inventory when completed
            if ($request->status === 'completed') {
                $product = Product::findOrFail($request->product_id);
                $previousProductStock = (float) $product->quantity;
                $newProductStock = round($previousProductStock + $preview['output_qty'], 3);

                $product->update([
                    'quantity' => $newProductStock,
                    'availablility' => $newProductStock > 0 ? 'in_stock' : 'out_stock',
                    'price' => $costPerUnit > 0 ? $costPerUnit : $product->price,
                ]);

                ProductInventory::create([
                    'product_id' => $product->id,
                    'initial_stock' => $previousProductStock,
                    'current_stock' => $newProductStock,
                    'branch_id' => $branchId,
                    'create_by' => $user->id,
                    'type' => 'Production Output',
                    'date' => now(),
                ]);
            }

            DB::commit();

            $messages = [
                'draft' => 'Production draft saved successfully.',
                'in_production' => 'Production started. Raw materials have been consumed from inventory.',
                'completed' => 'Production completed successfully.',
            ];

            return response()->json([
                'status' => true,
                'message' => $messages[$request->status] ?? 'Production saved.',
                'data' => $production->load(['items.rawMaterial', 'product', 'bom']),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateProduction(Request $request, $id)
    {
        $branchId = $this->resolveBranchId($request);
        $user = Auth::guard('api')->user();

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'production_qty' => 'required|numeric|gt:0',
            'production_date' => 'required|date',
            'status' => 'required|in:draft,in_production,completed',
            'wastage_percentage' => 'nullable|numeric|min:0|max:100',
            'labor_cost' => 'nullable|numeric|min:0',
            'electricity_cost' => 'nullable|numeric|min:0',
            'extra_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'batch_no' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $production = Production::with('items')
            ->where('branch_id', $branchId)
            ->findOrFail($id);

        $previousStatus = $production->status;

        if ($previousStatus === 'completed') {
            return response()->json([
                'status' => false,
                'message' => 'Completed production cannot be edited because inventory is already posted.',
            ], 422);
        }

        // Prevent going backwards: in_production cannot revert to draft
        if ($previousStatus === 'in_production' && $request->status === 'draft') {
            return response()->json([
                'status' => false,
                'message' => 'Cannot revert to draft. Raw materials have already been consumed from inventory.',
            ], 422);
        }

        $bom = Bom::with([
            'product:id,name,unit_id,quantity,branch_id',
            'items.rawMaterial:id,row_materialname,unit_id,price,quantity,branch_id,availablility',
            'items.rawMaterial.unit:id,unit_name',
            'items.unit:id,unit_name',
        ])
            ->where('branch_id', $branchId)
            ->where('product_id', $request->product_id)
            ->where('status', 'active')
            ->first();

        if (! $bom) {
            return response()->json([
                'status' => false,
                'message' => 'No active BOM found for the selected product.',
            ], 404);
        }

        $wastageOverride = $request->has('wastage_percentage') ? (float) $request->wastage_percentage : null;
        $preview = $this->buildProductionPreview($bom, (float) $request->production_qty, $wastageOverride);
        $laborCost = round((float) ($request->labor_cost ?? 0), 2);
        $electricityCost = round((float) ($request->electricity_cost ?? 0), 2);
        $extraCost = round((float) ($request->extra_cost ?? 0), 2);
        $totalCost = round($preview['material_cost'] + $laborCost + $electricityCost + $extraCost, 2);
        $costPerUnit = $preview['output_qty'] > 0 ? round($totalCost / $preview['output_qty'], 4) : 0;

        // Check stock only when transitioning from draft to in_production or completed
        $needsStockCheck = $previousStatus === 'draft' && in_array($request->status, ['in_production', 'completed']);
        if ($needsStockCheck && ! empty($preview['insufficient_items'])) {
            return response()->json([
                'status' => false,
                'message' => 'Insufficient stock for: ' . implode(', ', $preview['insufficient_items']),
            ], 422);
        }

        DB::beginTransaction();

        try {
            $production->update([
                'product_id' => $request->product_id,
                'bom_id' => $bom->id,
                'production_qty' => $preview['production_qty'],
                'output_qty' => $preview['output_qty'],
                'wastage_qty' => $preview['wastage_qty'],
                'wastage_percentage' => $preview['bom']['wastage_percentage'],
                'extra_cost' => $extraCost,
                'labor_cost' => $laborCost,
                'electricity_cost' => $electricityCost,
                'total_cost' => $totalCost,
                'cost_per_unit' => $costPerUnit,
                'production_date' => Carbon::parse($request->production_date)->format('Y-m-d'),
                'status' => $request->status,
                'batch_no' => $request->batch_no,
                'expiry_date' => $request->expiry_date ? Carbon::parse($request->expiry_date)->format('Y-m-d') : null,
                'notes' => $request->notes,
            ]);

            ProductionItem::where('production_id', $production->id)->delete();

            foreach ($preview['items'] as $item) {
                ProductionItem::create([
                    'production_id' => $production->id,
                    'raw_material_id' => $item['raw_material_id'],
                    'required_qty' => $item['required_qty'],
                    'consume_qty' => $item['consume_qty'],
                    'rate' => $item['rate'],
                    'total_cost' => $item['total_cost'],
                ]);
            }

            // draft → in_production: deduct raw materials (production started)
            // draft → completed: deduct raw materials (direct completion)
            if ($previousStatus === 'draft' && in_array($request->status, ['in_production', 'completed'])) {
                foreach ($preview['items'] as $item) {
                    $material = RowMaterial::findOrFail($item['raw_material_id']);
                    $previousStock = (float) $material->quantity;
                    $newStock = round($previousStock - $item['consume_qty'], 3);

                    $material->update([
                        'quantity' => $newStock,
                        'availablility' => $newStock > 0 ? 'in_stock' : 'out_stock',
                    ]);

                    RowMaterialInventory::create([
                        'row_material_id' => $material->id,
                        'initial_stock' => $previousStock,
                        'current_stock' => $newStock,
                        'branch_id' => $branchId,
                        'create_by' => $user->id,
                        'type' => $request->status === 'in_production' ? 'Production In-Progress' : 'Production Consume',
                        'date' => now(),
                    ]);
                }
            }

            // in_production → completed: add finished product to inventory
            // draft → completed: also add finished product
            if ($request->status === 'completed' && $previousStatus !== 'completed') {
                $product = Product::findOrFail($request->product_id);
                $previousProductStock = (float) $product->quantity;
                $newProductStock = round($previousProductStock + $preview['output_qty'], 3);

                $product->update([
                    'quantity' => $newProductStock,
                    'availablility' => $newProductStock > 0 ? 'in_stock' : 'out_stock',
                    'price' => $costPerUnit > 0 ? $costPerUnit : $product->price,
                ]);

                ProductInventory::create([
                    'product_id' => $product->id,
                    'initial_stock' => $previousProductStock,
                    'current_stock' => $newProductStock,
                    'branch_id' => $branchId,
                    'create_by' => $user->id,
                    'type' => 'Production Output',
                    'date' => now(),
                ]);
            }

            DB::commit();

            $messages = [
                'draft' => 'Production draft updated successfully.',
                'in_production' => 'Production started. Raw materials have been consumed from inventory.',
                'completed' => 'Production updated and completed successfully.',
            ];

            return response()->json([
                'status' => true,
                'message' => $messages[$request->status] ?? 'Production updated.',
                'data' => $production->load(['items.rawMaterial', 'product', 'bom']),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function deleteProduction(Request $request, $id)
    {
        $branchId = $this->resolveBranchId($request);

        $production = Production::where('branch_id', $branchId)->findOrFail($id);

        if (in_array($production->status, ['in_production', 'completed'])) {
            return response()->json([
                'status' => false,
                'message' => 'Production cannot be deleted because raw materials have already been consumed from inventory.',
            ], 422);
        }

        DB::beginTransaction();

        try {
            ProductionItem::where('production_id', $production->id)->delete();
            $production->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Production deleted successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
