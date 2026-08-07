<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\StoreSalesOrderRequest;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SalesOrder;
use App\Services\SalesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function __construct(protected SalesService $salesService) {}

    public function customers(): JsonResponse
    {
        $customers = Customer::latest()->paginate(20);
        return response()->json($customers);
    }

    public function storeCustomer(StoreCustomerRequest $request): JsonResponse
    {
        $customer = Customer::create($request->validated());
        return response()->json($customer, 201);
    }

    public function showCustomer(Customer $customer): JsonResponse
    {
        $customer->load('salesOrders');
        return response()->json($customer);
    }

    public function updateCustomer(StoreCustomerRequest $request, Customer $customer): JsonResponse
    {
        $customer->update($request->validated());
        return response()->json($customer);
    }

    public function destroyCustomer(Customer $customer): JsonResponse
    {
        $customer->delete();
        return response()->json(['message' => 'Customer deleted']);
    }

    public function orders(): JsonResponse
    {
        $orders = SalesOrder::with('customer', 'items.product', 'creator')->latest()->paginate(20);
        return response()->json($orders);
    }

    public function storeOrder(StoreSalesOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['created_by'] = auth()->id();
        $order = $this->salesService->createOrder($data);
        return response()->json($order->load('customer', 'items.product'), 201);
    }

    public function showOrder(SalesOrder $salesOrder): JsonResponse
    {
        $salesOrder->load('customer', 'items.product', 'deliveries', 'invoices.payments', 'creator');
        return response()->json($salesOrder);
    }

    public function invoices(): JsonResponse
    {
        $invoices = Invoice::with('order.customer', 'payments')->latest()->paginate(20);
        return response()->json($invoices);
    }

    public function generateInvoice(Request $request, SalesOrder $salesOrder): JsonResponse
    {
        $invoice = $this->salesService->generateInvoice($salesOrder, $request->all());
        return response()->json($invoice, 201);
    }

    public function processPayment(Request $request, Invoice $invoice): JsonResponse
    {
        $payment = $this->salesService->processPayment($invoice, $request->validate([
            'amount' => 'required|numeric|min:0',
            'method' => 'required|string',
            'reference' => 'nullable|string',
            'notes' => 'nullable|string',
        ]));
        return response()->json($payment, 201);
    }
}
