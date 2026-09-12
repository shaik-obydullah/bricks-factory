<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function index(): JsonResponse
    {
        $machines = Machine::latest()->get();
        return response()->json($machines);
    }

    public function store(Request $request): JsonResponse
    {
        $machine = Machine::create($request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:active,inactive,maintenance',
            'last_maintenance' => 'nullable|date',
            'next_maintenance' => 'nullable|date',
        ]));
        return response()->json($machine, 201);
    }

    public function show(Machine $machine): JsonResponse
    {
        return response()->json($machine);
    }

    public function update(Request $request, Machine $machine): JsonResponse
    {
        $machine->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:active,inactive,maintenance',
            'last_maintenance' => 'nullable|date',
            'next_maintenance' => 'nullable|date',
        ]));
        return response()->json($machine);
    }

    public function destroy(Machine $machine): JsonResponse
    {
        $machine->delete();
        return response()->json(['message' => 'Machine deleted']);
    }
}
