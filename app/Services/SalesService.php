<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;

class SalesService
{
    public function createOrder(array $data): SalesOrder
    {
        $data['order_number'] = $data['order_number'] ?? 'SO-' . now()->format('YmdHis');
        $data['status'] = $data['status'] ?? 'draft';
        $data['total_amount'] = $data['total_amount'] ?? 0;
        $data['discount'] = $data['discount'] ?? 0;
        $data['tax'] = $data['tax'] ?? 0;
        $data['final_amount'] = $data['final_amount'] ?? 0;

        return DB::transaction(function () use ($data) {
            $order = SalesOrder::create($data);

            if (!empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $order->items()->create($item);
                }

                $total = $order->items->sum('total');
                $finalAmount = $total - $order->discount + $order->tax;
                $order->update([
                    'total_amount' => $total,
                    'final_amount' => $finalAmount,
                ]);
            }

            return $order->fresh();
        });
    }

    public function generateInvoice(SalesOrder $order, array $data = []): Invoice
    {
        return Invoice::create([
            'order_id' => $order->id,
            'invoice_number' => $data['invoice_number'] ?? 'INV-' . now()->format('YmdHis'),
            'invoice_date' => $data['invoice_date'] ?? now(),
            'due_date' => $data['due_date'] ?? now()->addDays(30),
            'amount' => $data['amount'] ?? $order->final_amount,
            'paid_amount' => 0,
            'status' => 'pending',
        ]);
    }

    public function processPayment(Invoice $invoice, array $data): Payment
    {
        $payment = DB::transaction(function () use ($invoice, $data) {
            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'payment_date' => $data['payment_date'] ?? now(),
                'amount' => $data['amount'],
                'method' => $data['method'],
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $invoice->increment('paid_amount', $data['amount']);

            if ($invoice->paid_amount >= $invoice->amount) {
                $invoice->update(['status' => 'paid']);
            } elseif ($invoice->paid_amount > 0) {
                $invoice->update(['status' => 'partial']);
            }

            return $payment;
        });

        return $payment;
    }

    public function getSalesReport(array $filters = []): array
    {
        $query = SalesOrder::with(['customer', 'items.product']);

        if (!empty($filters['from'])) {
            $query->where('order_date', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->where('order_date', '<=', $filters['to']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $orders = $query->get();

        return [
            'total_orders' => $orders->count(),
            'total_revenue' => $orders->sum('final_amount'),
            'orders' => $orders,
        ];
    }
}
