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

    public function customers(Request $request): JsonResponse
    {
        $search = $request->input('search');
        $company = $request->input('company');

        $customers = Customer::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('company', 'like', "%{$search}%");
                });
            })
            ->when($company, function ($query) use ($company) {
                $query->where('company', $company);
            })
            ->latest()
            ->paginate(20);

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

    public function orders(Request $request): JsonResponse
    {
        $orders = SalesOrder::with('customer', 'items.product', 'creator')
            ->when($request->input('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->latest()
            ->paginate(20);
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

    public function updateOrder(StoreSalesOrderRequest $request, SalesOrder $salesOrder): JsonResponse
    {
        $data = $request->validated();

        $salesOrder = \Illuminate\Support\Facades\DB::transaction(function () use ($salesOrder, $data) {
            $salesOrder->update([
                'customer_id' => $data['customer_id'],
                'order_date' => $data['order_date'],
                'delivery_date' => $data['delivery_date'] ?? null,
                'status' => $data['status'] ?? $salesOrder->status,
                'notes' => $data['notes'] ?? null,
                'discount' => $data['discount'] ?? $salesOrder->discount,
                'tax' => $data['tax'] ?? $salesOrder->tax,
            ]);

            if (!empty($data['items'])) {
                $salesOrder->items()->delete();
                foreach ($data['items'] as $item) {
                    $salesOrder->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total' => $item['quantity'] * $item['unit_price'],
                    ]);
                }
                $total = $salesOrder->items->sum('total');
                $salesOrder->update([
                    'total_amount' => $total,
                    'final_amount' => $total - $salesOrder->discount + $salesOrder->tax,
                ]);
            }

            return $salesOrder;
        });

        return response()->json($salesOrder->load('customer', 'items.product'));
    }

    public function destroyOrder(SalesOrder $salesOrder): JsonResponse
    {
        $salesOrder->delete();
        return response()->json(['message' => 'Sales order deleted']);
    }

    public function invoices(Request $request): JsonResponse
    {
        $invoices = Invoice::with('order.customer', 'payments')
            ->when($request->input('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->when($request->input('date_from'), function ($query) use ($request) {
                $query->whereDate('invoice_date', '>=', $request->input('date_from'));
            })
            ->when($request->input('date_to'), function ($query) use ($request) {
                $query->whereDate('invoice_date', '<=', $request->input('date_to'));
            })
            ->when($request->input('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhereHas('order.customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest()
            ->paginate(20);

        return response()->json($invoices);
    }

    public function showInvoice(Invoice $invoice): JsonResponse
    {
        $invoice->load('order.customer', 'order.items.product', 'payments');
        return response()->json($invoice);
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

    public function storeInvoice(Request $request): JsonResponse
    {
        $data = $request->validate([
            'sales_order_id' => 'required|exists:sales_orders,id',
            'invoice_date' => 'nullable|date',
            'status' => 'nullable|string|in:draft,sent,paid,overdue,cancelled',
        ]);

        $order = SalesOrder::findOrFail($data['sales_order_id']);
        $invoice = $this->salesService->generateInvoice($order, [
            'invoice_date' => $data['invoice_date'] ?? now(),
        ]);
        $invoice->update(['status' => $data['status'] ?? 'draft']);

        return response()->json($invoice->load('order.customer', 'payments'), 201);
    }

    public function updateInvoice(Request $request, Invoice $invoice): JsonResponse
    {
        $invoice->update($request->validate([
            'sales_order_id' => 'nullable|exists:sales_orders,id',
            'invoice_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'status' => 'nullable|string|in:draft,sent,paid,overdue,cancelled',
        ]));
        return response()->json($invoice->load('order.customer', 'payments'));
    }

    public function destroyInvoice(Invoice $invoice): JsonResponse
    {
        $invoice->delete();
        return response()->json(['message' => 'Invoice deleted']);
    }

    public function payments(Request $request): JsonResponse
    {
        $payments = Payment::with('invoice.order.customer')
            ->when($request->input('date_from'), function ($query) use ($request) {
                $query->whereDate('payment_date', '>=', $request->input('date_from'));
            })
            ->when($request->input('date_to'), function ($query) use ($request) {
                $query->whereDate('payment_date', '<=', $request->input('date_to'));
            })
            ->latest()
            ->paginate(20);
        return response()->json($payments);
    }

    public function storePayment(Request $request): JsonResponse
    {
        $data = $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'nullable|date',
            'payment_method' => 'nullable|string',
            'reference' => 'nullable|string',
        ]);

        $invoice = Invoice::findOrFail($data['invoice_id']);
        $payment = $this->salesService->processPayment($invoice, [
            'payment_date' => $data['payment_date'] ?? now(),
            'amount' => $data['amount'],
            'method' => $data['payment_method'] ?? 'cash',
            'reference' => $data['reference'] ?? null,
        ]);

        return response()->json($payment->load('invoice.order.customer'), 201);
    }

    public function destroyPayment(Payment $payment): JsonResponse
    {
        $payment->delete();
        return response()->json(['message' => 'Payment deleted']);
    }
}
