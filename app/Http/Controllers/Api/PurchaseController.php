<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePurchaseOrderRequest;
use App\Http\Requests\StoreSupplierRequest;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\PurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    public function __construct(protected PurchaseService $purchaseService) {}

    public function suppliers(): JsonResponse
    {
        $suppliers = Supplier::latest()->paginate(20);
        return response()->json($suppliers);
    }

    public function storeSupplier(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = Supplier::create($request->validated());
        return response()->json($supplier, 201);
    }

    public function showSupplier(Supplier $supplier): JsonResponse
    {
        $supplier->load('purchaseOrders');
        return response()->json($supplier);
    }

    public function updateSupplier(StoreSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $supplier->update($request->validated());
        return response()->json($supplier);
    }

    public function destroySupplier(Supplier $supplier): JsonResponse
    {
        $supplier->delete();
        return response()->json(['message' => 'Supplier deleted']);
    }

    public function orders(): JsonResponse
    {
        $orders = PurchaseOrder::with('supplier', 'items.material', 'creator')->latest()->paginate(20);
        return response()->json($orders);
    }

    public function storeOrder(StorePurchaseOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $order = $this->purchaseService->createOrder($data);
        return response()->json($order->load('supplier', 'items.material'), 201);
    }

    public function showOrder(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $purchaseOrder->load('supplier', 'items.material', 'goodsReceipts', 'purchaseInvoices', 'creator');
        return response()->json($purchaseOrder);
    }

    public function receiveGoods(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $receipt = $this->purchaseService->receiveGoods($purchaseOrder, $request->validate([
            'receipt_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]));
        return response()->json($receipt, 201);
    }
}
