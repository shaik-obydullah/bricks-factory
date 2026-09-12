<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index(): JsonResponse
    {
        $shifts = Shift::latest()->get();
        return response()->json($shifts);
    }

    public function store(Request $request): JsonResponse
    {
        $shift = Shift::create($request->validate([
            'name' => 'required|string|max:255',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'status' => 'nullable|string|in:active,inactive',
        ]));
        return response()->json($shift, 201);
    }

    public function show(Shift $shift): JsonResponse
    {
        return response()->json($shift);
    }

    public function update(Request $request, Shift $shift): JsonResponse
    {
        $shift->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'start_time' => 'sometimes|date_format:H:i',
            'end_time' => 'sometimes|date_format:H:i',
            'status' => 'nullable|string|in:active,inactive',
        ]));
        return response()->json($shift);
    }

    public function destroy(Shift $shift): JsonResponse
    {
        $shift->delete();
        return response()->json(['message' => 'Shift deleted']);
    }
}
