<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBatchRequest;
use App\Http\Requests\StoreProductionOrderRequest;
use App\Models\Machine;
use App\Models\ProductionBatch;
use App\Models\ProductionOrder;
use App\Models\ProductionTarget;
use App\Services\ProductionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionController extends Controller
{
    public function __construct(protected ProductionService $productionService) {}

    public function index(Request $request): JsonResponse
    {
        $orders = ProductionOrder::with('product', 'batches')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('date_from'), fn ($q) => $q->whereDate('planned_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($q) => $q->whereDate('planned_date', '<=', $request->date_to))
            ->latest()
            ->paginate(20);
        return response()->json($orders);
    }

    public function store(StoreProductionOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $order = $this->productionService->createOrder($data);
        return response()->json($order, 201);
    }

    public function show(ProductionOrder $productionOrder): JsonResponse
    {
        $productionOrder->load('product', 'batches.qualityChecks', 'batches.machine', 'batches.shift', 'creator');
        return response()->json($productionOrder);
    }

    public function update(StoreProductionOrderRequest $request, ProductionOrder $productionOrder): JsonResponse
    {
        $order = $this->productionService->updateOrder($productionOrder, $request->validated());
        return response()->json($order);
    }

    public function destroy(ProductionOrder $productionOrder): JsonResponse
    {
        $productionOrder->delete();
        return response()->json(['message' => 'Order deleted']);
    }

    public function batches(ProductionOrder $productionOrder): JsonResponse
    {
        $batches = $productionOrder->batches()->with('shift', 'machine', 'operator')->latest()->get();
        return response()->json($batches);
    }

    public function batchesIndex(): JsonResponse
    {
        $batches = ProductionBatch::with('order.product', 'shift', 'machine', 'operator')
            ->latest()
            ->paginate(20);

        return response()->json($batches);
    }

    public function startBatch(StoreBatchRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['order_id'] = $data['order_id'] ?? $data['production_order_id'] ?? null;
        unset($data['production_order_id']);

        if (empty($data['shift_id']) && !empty($data['shift'])) {
            $shift = \App\Models\Shift::whereRaw('LOWER(name) = ?', [strtolower($data['shift'])])->first();
            $data['shift_id'] = $shift?->id;
        }
        unset($data['shift']);

        $data['quantity_produced'] = $data['quantity'] ?? 0;
        unset($data['quantity']);

        $batch = $this->productionService->startBatch($data);
        return response()->json($batch, 201);
    }

    public function completeBatch(Request $request, ProductionBatch $productionBatch): JsonResponse
    {
        $batch = $this->productionService->completeBatch($productionBatch, $request->validate([
            'quantity_produced' => 'required|numeric|min:0',
            'quantity_rejected' => 'nullable|numeric|min:0',
        ]));
        return response()->json($batch);
    }

    public function machines(): JsonResponse
    {
        $machines = Machine::latest()->get();
        return response()->json($machines);
    }

    public function storeMachine(Request $request): JsonResponse
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

    public function targets(): JsonResponse
    {
        $targets = ProductionTarget::with('product')->latest()->get();
        return response()->json($targets);
    }

    public function storeTarget(Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'target_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'target_quantity' => 'required|numeric|min:0',
            'actual_quantity' => 'nullable|numeric|min:0',
        ]);
        $data['target_date'] = $data['target_date'] ?? $data['start_date'] ?? now();
        $target = ProductionTarget::create($data);
        return response()->json($target->load('product'), 201);
    }

    public function updateTarget(Request $request, ProductionTarget $productionTarget): JsonResponse
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'target_date' => 'nullable|date',
            'start_date' => 'nullable|date',
            'target_quantity' => 'required|numeric|min:0',
            'actual_quantity' => 'nullable|numeric|min:0',
        ]);
        $data['target_date'] = $data['target_date'] ?? $data['start_date'] ?? $productionTarget->target_date;
        unset($data['start_date']);
        $productionTarget->update($data);
        return response()->json($productionTarget->load('product'));
    }

    public function destroyTarget(ProductionTarget $productionTarget): JsonResponse
    {
        $productionTarget->delete();
        return response()->json(['message' => 'Target deleted']);
    }
}
