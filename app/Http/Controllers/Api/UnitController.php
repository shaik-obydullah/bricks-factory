<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index(): JsonResponse
    {
        $units = Unit::latest()->get();
        return response()->json($units);
    }

    public function store(Request $request): JsonResponse
    {
        $unit = Unit::create($request->validate([
            'name' => 'required|string|max:255',
            'symbol' => 'required|string|max:20',
            'status' => 'nullable|string|in:active,inactive',
        ]));
        return response()->json($unit, 201);
    }

    public function show(Unit $unit): JsonResponse
    {
        return response()->json($unit);
    }

    public function update(Request $request, Unit $unit): JsonResponse
    {
        $unit->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'symbol' => 'sometimes|string|max:20',
            'status' => 'nullable|string|in:active,inactive',
        ]));
        return response()->json($unit);
    }

    public function destroy(Unit $unit): JsonResponse
    {
        $unit->delete();
        return response()->json(['message' => 'Unit deleted']);
    }
}
