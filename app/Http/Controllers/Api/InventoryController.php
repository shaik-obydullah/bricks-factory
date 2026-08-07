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

    public function products(): JsonResponse
    {
        $products = Product::with('category', 'productStocks.warehouse')->latest()->paginate(20);
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

    public function stockMovements(): JsonResponse
    {
        $movements = StockMovement::with('typeable', 'creator')->latest()->paginate(20);
        return response()->json($movements);
    }

    public function stockAlerts(): JsonResponse
    {
        return response()->json($this->inventoryService->getStockAlerts());
    }

    public function updateStock(Request $request): JsonResponse
    {
        $data = $request->validate([
            'typeable_type' => 'required|string',
            'typeable_id' => 'required|integer',
            'movement_type' => 'required|string|in:in,out,transfer',
            'quantity' => 'required|numeric|min:0',
            'reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $typeable = $data['typeable_type']::findOrFail($data['typeable_id']);
        $movement = $this->inventoryService->updateStock($typeable, $data['movement_type'], $data['quantity'], $data);

        return response()->json($movement, 201);
    }
}
