<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(): JsonResponse
    {
        $warehouses = Warehouse::latest()->get();
        return response()->json($warehouses);
    }

    public function store(Request $request): JsonResponse
    {
        $warehouse = Warehouse::create($request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:active,inactive',
        ]));
        return response()->json($warehouse, 201);
    }

    public function show(Warehouse $warehouse): JsonResponse
    {
        $warehouse->load('productStocks.product');
        return response()->json($warehouse);
    }

    public function update(Request $request, Warehouse $warehouse): JsonResponse
    {
        $warehouse->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|numeric|min:0',
            'status' => 'nullable|string|in:active,inactive',
        ]));
        return response()->json($warehouse);
    }

    public function destroy(Warehouse $warehouse): JsonResponse
    {
        $warehouse->delete();
        return response()->json(['message' => 'Warehouse deleted']);
    }
}
