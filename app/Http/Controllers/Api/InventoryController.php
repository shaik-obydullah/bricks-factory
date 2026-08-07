<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\RawMaterial;
use App\Models\StockMovement;
use App\Services\InventoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(protected InventoryService $inventoryService) {}

    public function products(Request $request): JsonResponse
    {
        $warehouseId = $request->integer('warehouse_id');

        $products = Product::query()
            ->with('category')
            ->when($warehouseId, function ($query) use ($warehouseId) {
                $query->whereHas('productStocks', fn ($q) => $q->where('warehouse_id', $warehouseId))
                    ->with(['productStocks' => fn ($q) => $q->where('warehouse_id', $warehouseId)->with('warehouse')]);
            }, function ($query) {
                $query->with('productStocks.warehouse');
            })
            ->latest()
            ->paginate(20);

        return response()->json($products);
    }

    public function storeProduct(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());
        return response()->json($product, 201);
    }

    public function showProduct(Product $product): JsonResponse
    {
        $product->load('category', 'productStocks.warehouse', 'stockMovements');
        return response()->json($product);
    }

    public function updateProduct(StoreProductRequest $request, Product $product): JsonResponse
    {
        $product->update($request->validated());
        return response()->json($product);
    }

    public function destroyProduct(Product $product): JsonResponse
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted']);
    }

    public function rawMaterials(): JsonResponse
    {
        $materials = RawMaterial::latest()->paginate(20);
        return response()->json($materials);
    }

    public function storeRawMaterial(Request $request): JsonResponse
    {
        $material = RawMaterial::create($request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:raw_materials,code',
            'unit' => 'nullable|string|max:50',
            'current_stock' => 'nullable|numeric|min:0',
            'minimum_stock' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:active,inactive',
        ]));
        return response()->json($material, 201);
    }

    public function stockMovements(Request $request): JsonResponse
    {
        $movements = StockMovement::query()
            ->with('typeable', 'creator')
            ->when($request->input('movement_type') ?? $request->input('type'), function ($query, $type) {
                $query->where('movement_type', $type);
            })
            ->when($request->input('typeable_type') ?? $request->input('movable_type'), function ($query, $typeableType) {
                $query->where('typeable_type', $typeableType);
            })
            ->latest()
            ->paginate(20);

        return response()->json($movements);
    }

    public function stockAlerts(): JsonResponse
    {
        return response()->json($this->inventoryService->getStockAlerts());
    }

    public function updateStock(Request $request): JsonResponse
    {
        $data = $request->validate([
            'typeable_type' => 'required_without:movable_type|string',
            'typeable_id' => 'required_without:movable_id|integer',
            'movable_type' => 'nullable|string',
            'movable_id' => 'nullable|integer',
            'movement_type' => 'required_without:type|string|in:in,out,transfer',
            'type' => 'nullable|string|in:in,out,transfer',
            'quantity' => 'required|numeric|min:0',
            'from_warehouse_id' => 'nullable|integer',
            'to_warehouse_id' => 'nullable|integer',
            'reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $data['typeable_type'] = $data['typeable_type'] ?? $data['movable_type'];
        $data['typeable_id'] = $data['typeable_id'] ?? $data['movable_id'];
        $data['movement_type'] = $data['movement_type'] ?? $data['type'];

        $typeable = $data['typeable_type']::findOrFail($data['typeable_id']);
        $movement = $this->inventoryService->updateStock($typeable, $data['movement_type'], $data['quantity'], $data);

        return response()->json($movement, 201);
    }
}
