<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Customer;
use App\Models\Defect;
use App\Models\Delivery;
use App\Models\Employee;
use App\Models\GoodsReceipt;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\ProductionBatch;
use App\Models\ProductionOrder;
use App\Models\ProductionTarget;
use App\Models\ProductStock;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\QualityCheck;
use App\Models\QualityCheckItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Setting;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = 1;
        $today = Carbon::today();

        // ------------------------------------------------------------------
        // Customers
        // ------------------------------------------------------------------
        $customers = [
            ['name' => 'Rahim Uddin', 'company' => 'Rahim Builders Ltd', 'phone' => '01711-112233', 'email' => 'rahim@rahimbuilders.com', 'address' => 'House 12, Road 5, Dhanmondi', 'city' => 'Dhaka'],
            ['name' => 'Karim Mia', 'company' => 'Karim Construction Co', 'phone' => '01812-445566', 'email' => 'karim@karimconst.com', 'address' => 'Mirpur 10', 'city' => 'Dhaka'],
            ['name' => 'Abdul Hakim', 'company' => 'Hakim Housing', 'phone' => '01913-778899', 'email' => 'hakim@hakimhousing.com', 'address' => 'Zindabahar', 'city' => 'Chattogram'],
            ['name' => 'Shahidul Islam', 'company' => 'Islam Developers', 'phone' => '01614-223344', 'email' => 'shahid@islamdev.com', 'address' => 'Kazir Dewri', 'city' => 'Chattogram'],
            ['name' => 'Nasir Ahmed', 'company' => 'Nasir Traders', 'phone' => '01515-556677', 'email' => 'nasir@nasirtraders.com', 'address' => 'Shah Makhdum Avenue', 'city' => 'Rajshahi'],
            ['name' => 'Mizanur Rahman', 'company' => 'Mizan Real Estate', 'phone' => '01716-889900', 'email' => 'mizan@mizanre.com', 'address' => 'Uposhohor', 'city' => 'Sylhet'],
        ];
        foreach ($customers as $c) {
            Customer::create($c);
        }

        // ------------------------------------------------------------------
        // Suppliers
        // ------------------------------------------------------------------
        $suppliers = [
            ['name' => 'Meghna Clay Industries', 'company' => 'Meghna Group', 'phone' => '01721-111111', 'email' => 'sales@meghnaclay.com', 'address' => 'Shimrail, Narayanganj'],
            ['name' => 'Padma Sand Suppliers', 'company' => 'Padma Sand Co', 'phone' => '01822-222222', 'email' => 'info@padmasand.com', 'address' => 'Ghat No 4, Aricha'],
            ['name' => 'Crown Cement Ltd', 'company' => 'Crown Cement', 'phone' => '01933-333333', 'email' => 'order@crowncement.com', 'address' => 'Tejgaon Industrial Area, Dhaka'],
            ['name' => 'Barapukuria Coal & Fly Ash', 'company' => 'BCMCL', 'phone' => '01644-444444', 'email' => 'flyash@bcmcl.gov.bd', 'address' => 'Barapukuria, Dinajpur'],
            ['name' => 'Jamuna Construction Materials', 'company' => 'Jamuna Materials', 'phone' => '01555-555555', 'email' => 'supply@jamunamat.com', 'address' => 'Tangail Road, Kalihati'],
        ];
        foreach ($suppliers as $s) {
            Supplier::create($s);
        }

        // ------------------------------------------------------------------
        // Employees + Attendance
        // ------------------------------------------------------------------
        $employees = [
            ['Rashed Khan', '01731-111222', 'rashed@bricks.com', 'Production Manager', 'Production', 1, '2022-01-10'],
            ['Sultana Akter', '01732-222333', 'sultana@bricks.com', 'QC Inspector', 'Quality Control', 1, '2022-03-01'],
            ['Faisal Mahmud', '01733-333444', 'faisal@bricks.com', 'Machine Operator', 'Production', 2, '2022-05-15'],
            ['Sakib Hasan', '01734-444555', 'sakib@bricks.com', 'Machine Operator', 'Production', 1, '2022-07-20'],
            ['Robiul Islam', '01735-555666', 'robiul@bricks.com', 'Kiln Operator', 'Production', 3, '2022-09-01'],
            ['Sumon Mia', '01736-666777', 'sumon@bricks.com', 'Dryer Operator', 'Production', 2, '2023-01-05'],
            ['Jahid Hasan', '01737-777888', 'jahid@bricks.com', 'Loader', 'Logistics', 1, '2023-02-10'],
            ['Nazmul Hossain', '01738-888999', 'nazmul@bricks.com', 'Store Keeper', 'Inventory', 1, '2023-04-01'],
            ['Mehedi Hassan', '01739-999000', 'mehedi@bricks.com', 'Electrician', 'Maintenance', 2, '2023-06-15'],
            ['Tania Sultana', '01740-101112', 'tania@bricks.com', 'HR Officer', 'HR', 1, '2023-08-01'],
            ['Kamal Hossain', '01741-121314', 'kamal@bricks.com', 'Driver', 'Logistics', 2, '2023-10-01'],
            ['Shakil Ahmed', '01742-141516', 'shakil@bricks.com', 'Machine Operator', 'Production', 3, '2024-01-10'],
            ['Anwar Hossain', '01743-151617', 'anwar@bricks.com', 'QC Inspector', 'Quality Control', 2, '2024-02-15'],
            ['Lima Begum', '01744-161718', 'lima@bricks.com', 'Assistant Store Keeper', 'Inventory', 1, '2024-04-01'],
            ['Babul Akter', '01745-171819', 'babul@bricks.com', 'Mould Worker', 'Production', 1, '2024-06-01'],
            ['Sabbir Rahman', '01746-181920', 'sabbir@bricks.com', 'Accountant', 'Finance', 1, '2024-08-01'],
        ];
        $employeeIds = [];
        foreach ($employees as $i => [$name, $phone, $email, $designation, $department, $shiftId, $joining]) {
            $emp = Employee::create([
                'employee_id' => sprintf('EMP-%03d', $i + 1),
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
                'designation' => $designation,
                'department' => $department,
                'shift_id' => $shiftId,
                'joining_date' => $joining,
                'status' => 'active',
            ]);
            $employeeIds[] = $emp->id;
        }

        for ($d = 10; $d >= 1; $d--) {
            $date = $today->copy()->subDays($d);
            if ($date->isFriday()) {
                continue;
            }
            foreach ($employeeIds as $idx => $empId) {
                $shiftStart = $employees[$idx][5] == 3 ? '22:00' : ($employees[$idx][5] == 2 ? '14:00' : '06:00');
                $shiftEnd = $employees[$idx][5] == 3 ? '06:00' : ($employees[$idx][5] == 2 ? '22:00' : '14:00');
                $checkOut = $employees[$idx][5] == 3 ? '06:00' : $shiftEnd;
                Attendance::create([
                    'employee_id' => $empId,
                    'date' => $date,
                    'check_in' => $shiftStart,
                    'check_out' => ($idx % 9 == 0) ? null : $checkOut,
                    'status' => ($idx % 9 == 0) ? 'absent' : 'present',
                    'notes' => ($idx % 9 == 0) ? 'Leave' : null,
                ]);
            }
        }

        // ------------------------------------------------------------------
        // Production Orders + Batches
        // ------------------------------------------------------------------
        $production = [
            // [product_id, quantity, planned_date_days_ago, status, priority]
            [1, 50000, 25, 'completed', 'high'],
            [2, 30000, 24, 'completed', 'medium'],
            [3, 12000, 22, 'completed', 'low'],
            [4, 40000, 20, 'completed', 'high'],
            [1, 60000, 15, 'in_progress', 'high'],
            [5, 8000, 13, 'confirmed', 'medium'],
            [2, 35000, 8, 'in_progress', 'high'],
            [4, 50000, 5, 'confirmed', 'medium'],
            [3, 10000, 2, 'draft', 'low'],
            [1, 55000, 0, 'draft', 'medium'],
        ];

        foreach ($production as $i => [$productId, $qty, $daysAgo, $status, $priority]) {
            $order = ProductionOrder::create([
                'order_number' => 'PO-' . date('Ymd', strtotime("-{$daysAgo} days")) . '-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'product_id' => $productId,
                'quantity' => $qty,
                'planned_date' => $today->copy()->subDays($daysAgo)->toDateString(),
                'status' => $status,
                'priority' => $priority,
                'notes' => 'Demo production order',
                'created_by' => $adminId,
            ]);

            if (in_array($status, ['completed', 'in_progress', 'confirmed', 'draft'])) {
                $done = match ($status) {
                    'completed' => $qty,
                    'in_progress' => (int) ($qty * 0.6),
                    'confirmed' => 0,
                    default => 0,
                };
                if ($done > 0) {
                    $batchQty = 0;
                    $batchCount = max(1, (int) round($done / 25000));
                    $producedPerBatch = (int) floor($done / $batchCount);
                    for ($b = 1; $b <= $batchCount; $b++) {
                        $isLast = $b === $batchCount;
                        $produced = $isLast ? $done - $batchQty : $producedPerBatch;
                        $batchQty += $produced;
                        $rejected = (int) round($produced * 0.015);
                        $start = $today->copy()->subDays($daysAgo)->addHours(($b - 1) * 8)->setTime(6, 0);
                        $completed = $status === 'completed';
                        ProductionBatch::create([
                            'order_id' => $order->id,
                            'shift_id' => (($i + $b) % 3) + 1,
                            'machine_id' => (($i + $b) % 5) + 1,
                            'quantity_produced' => $produced,
                            'quantity_rejected' => $rejected,
                            'start_time' => $start,
                            'end_time' => $completed ? $start->copy()->addHours(8) : null,
                            'status' => $completed ? 'completed' : 'running',
                            'operator_id' => $adminId,
                        ]);
                    }
                }
            }
        }

        // ------------------------------------------------------------------
        // Production Targets (current month)
        // ------------------------------------------------------------------
        $targets = [
            [1, 150000, 110000],
            [2, 80000, 35000],
            [3, 30000, 12000],
            [4, 120000, 40000],
            [5, 20000, 0],
        ];
        foreach ($targets as $i => [$productId, $target, $actual]) {
            ProductionTarget::create([
                'product_id' => $productId,
                'target_date' => $today->copy()->endOfMonth()->toDateString(),
                'target_quantity' => $target,
                'actual_quantity' => $actual,
            ]);
        }

        // ------------------------------------------------------------------
        // Sales Orders + Items + Invoices + Payments + Deliveries
        // ------------------------------------------------------------------
        $sales = [
            // [customer_idx(1-based), order_date_days_ago, delivery_days_ago, status, items[[product_id,qty,price]]]
            [1, 30, 27, 'completed', [[1, 20000, 9.50], [4, 15000, 7.25]]],
            [2, 26, 24, 'completed', [[2, 15000, 12.00]]],
            [3, 22, null, 'in_progress', [[3, 5000, 22.00], [5, 2000, 18.00]]],
            [4, 18, 16, 'completed', [[4, 20000, 7.25]]],
            [5, 12, null, 'confirmed', [[1, 30000, 9.50]]],
            [1, 9, 6, 'completed', [[2, 10000, 12.00], [3, 3000, 22.00]]],
            [6, 4, null, 'in_progress', [[4, 25000, 7.25], [1, 10000, 9.50]]],
            [2, 1, null, 'draft', [[5, 1500, 18.00]]],
        ];

        foreach ($sales as $i => [$custIdx, $orderDaysAgo, $delivDaysAgo, $status, $items]) {
            $subtotal = 0;
            $rows = [];
            foreach ($items as [$productId, $qty, $price]) {
                $total = $qty * $price;
                $subtotal += $total;
                $rows[] = ['product_id' => $productId, 'quantity' => $qty, 'unit_price' => $price, 'total' => $total];
            }
            $discount = round($subtotal * 0.02, 2);
            $tax = round(($subtotal - $discount) * 0.05, 2);
            $final = round($subtotal - $discount + $tax, 2);
            $order = SalesOrder::create([
                'order_number' => 'SO-' . date('Ymd', strtotime("-{$orderDaysAgo} days")) . '-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'customer_id' => $custIdx,
                'order_date' => $today->copy()->subDays($orderDaysAgo)->toDateString(),
                'delivery_date' => $delivDaysAgo !== null ? $today->copy()->subDays($delivDaysAgo)->toDateString() : null,
                'status' => $status,
                'total_amount' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'final_amount' => $final,
                'notes' => 'Demo sales order',
                'created_by' => $adminId,
            ]);
            foreach ($rows as $r) {
                SalesOrderItem::create(['order_id' => $order->id] + $r);
            }

            if (in_array($status, ['completed', 'in_progress'])) {
                $invoice = Invoice::create([
                    'order_id' => $order->id,
                    'invoice_number' => 'INV-' . str_pad((string) (1000 + $i + 1), 5, '0', STR_PAD_LEFT),
                    'invoice_date' => $today->copy()->subDays($orderDaysAgo)->toDateString(),
                    'due_date' => $today->copy()->subDays(max(0, $orderDaysAgo - 15))->toDateString(),
                    'amount' => $final,
                    'paid_amount' => $status === 'completed' ? $final : round($final * 0.5, 2),
                    'status' => $status === 'completed' ? 'paid' : 'partial',
                ]);
                Payment::create([
                    'invoice_id' => $invoice->id,
                    'payment_date' => $today->copy()->subDays(max(0, $orderDaysAgo - 5))->toDateString(),
                    'amount' => $invoice->paid_amount,
                    'method' => ['bkash', 'nagad', 'bank', 'cash'][$i % 4],
                    'reference' => 'TXN-' . random_int(100000, 999999),
                    'notes' => 'Demo payment',
                ]);

                if ($delivDaysAgo !== null) {
                    Delivery::create([
                        'order_id' => $order->id,
                        'delivery_date' => $today->copy()->subDays($delivDaysAgo)->toDateString(),
                        'status' => 'delivered',
                        'vehicle_number' => ['Dhaka Metro-11-4455', 'Chattogram-12-6677', 'Dhaka Metro-15-8899'][$i % 3],
                        'driver_name' => $employees[10][0],
                        'notes' => 'Delivered to customer site',
                    ]);
                }
            }
        }

        // ------------------------------------------------------------------
        // Purchase Orders + Items + Goods Receipts + Purchase Invoices
        // ------------------------------------------------------------------
        $purchases = [
            // [supplier_idx, order_days_ago, expected_days_ago, status, items[[material_id, qty, price]]]
            [1, 28, 25, 'completed', [[1, 200, 1800.00], [2, 100, 950.00]]],
            [3, 20, null, 'confirmed', [[3, 200, 520.00]]],
            [4, 15, 13, 'completed', [[4, 150, 1200.00]]],
            [2, 10, 8, 'completed', [[2, 150, 950.00]]],
            [5, 5, null, 'confirmed', [[1, 250, 1750.00]]],
            [1, 1, null, 'draft', [[1, 150, 1800.00]]],
        ];

        foreach ($purchases as $i => [$supplierIdx, $orderDaysAgo, $expectedDaysAgo, $status, $items]) {
            $total = 0;
            $rows = [];
            foreach ($items as [$materialId, $qty, $price]) {
                $lineTotal = $qty * $price;
                $total += $lineTotal;
                $rows[] = ['material_id' => $materialId, 'quantity' => $qty, 'unit_price' => $price, 'total' => $lineTotal];
            }
            $order = PurchaseOrder::create([
                'order_number' => 'PR-' . date('Ymd', strtotime("-{$orderDaysAgo} days")) . '-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
                'supplier_id' => $supplierIdx,
                'order_date' => $today->copy()->subDays($orderDaysAgo)->toDateString(),
                'expected_date' => $expectedDaysAgo !== null ? $today->copy()->subDays($expectedDaysAgo)->toDateString() : $today->copy()->addDays(10)->toDateString(),
                'status' => $status,
                'total_amount' => $total,
                'notes' => 'Demo purchase order',
                'created_by' => $adminId,
            ]);
            foreach ($rows as $r) {
                PurchaseOrderItem::create(['order_id' => $order->id] + $r);
            }

            if ($status === 'completed') {
                GoodsReceipt::create([
                    'order_id' => $order->id,
                    'receipt_date' => $today->copy()->subDays($expectedDaysAgo)->toDateString(),
                    'received_by' => $adminId,
                    'status' => 'received',
                    'notes' => 'Goods received and stored',
                ]);
                PurchaseInvoice::create([
                    'order_id' => $order->id,
                    'invoice_number' => 'PI-' . str_pad((string) (5000 + $i + 1), 5, '0', STR_PAD_LEFT),
                    'invoice_date' => $today->copy()->subDays($expectedDaysAgo)->toDateString(),
                    'amount' => $total,
                    'paid_amount' => $total,
                    'status' => 'paid',
                ]);
            } elseif ($status === 'confirmed') {
                PurchaseInvoice::create([
                    'order_id' => $order->id,
                    'invoice_number' => 'PI-' . str_pad((string) (5000 + $i + 1), 5, '0', STR_PAD_LEFT),
                    'invoice_date' => $today->copy()->subDays(2)->toDateString(),
                    'amount' => $total,
                    'paid_amount' => 0,
                    'status' => 'pending',
                ]);
            }
        }

        // ------------------------------------------------------------------
        // Quality Checks + Items + Defects (for completed batches)
        // ------------------------------------------------------------------
        foreach (ProductionBatch::with('order')->where('status', 'completed')->get() as $batch) {
            $check = QualityCheck::create([
                'batch_id' => $batch->id,
                'check_date' => $batch->start_time->toDateString(),
                'inspector_id' => $adminId,
                'status' => 'passed',
                'notes' => 'Routine batch inspection',
            ]);
            $dimensionOk = ($batch->id % 5) !== 0;
            QualityCheckItem::create(['check_id' => $check->id, 'parameter' => 'Compressive Strength', 'expected_value' => '>= 10 MPa', 'actual_value' => round(10.5 + ($batch->id % 5), 1) . ' MPa', 'status' => 'passed']);
            QualityCheckItem::create(['check_id' => $check->id, 'parameter' => 'Dimensions', 'expected_value' => '240x115x70 mm', 'actual_value' => $dimensionOk ? '240x115x70 mm' : '238x115x70 mm', 'status' => $dimensionOk ? 'passed' : 'failed']);
            QualityCheckItem::create(['check_id' => $check->id, 'parameter' => 'Water Absorption', 'expected_value' => '<= 15%', 'actual_value' => round(12 + ($batch->id % 3), 1) . '%', 'status' => 'passed']);

            if ($batch->quantity_rejected > 0) {
                Defect::create([
                    'batch_id' => $batch->id,
                    'type' => 'Cracked surface',
                    'severity' => 'medium',
                    'description' => round($batch->quantity_rejected * 0.5) . ' pcs rejected for surface cracks during drying.',
                    'status' => 'resolved',
                    'resolved_by' => $adminId,
                    'resolved_at' => $batch->start_time->copy()->addDay(),
                ]);
                if ($batch->id % 3 === 0) {
                    Defect::create([
                        'batch_id' => $batch->id,
                        'type' => 'Dimension deviation',
                        'severity' => 'low',
                        'description' => round($batch->quantity_rejected * 0.3) . ' pcs outside tolerance.',
                        'status' => 'open',
                    ]);
                }
            }
        }

        // ------------------------------------------------------------------
        // Product Stocks + Stock Movements
        // ------------------------------------------------------------------
        $stockMap = [
            1 => ['warehouse' => 2, 'qty' => 85000],
            2 => ['warehouse' => 2, 'qty' => 25000],
            3 => ['warehouse' => 1, 'qty' => 8000],
            4 => ['warehouse' => 2, 'qty' => 40000],
            5 => ['warehouse' => 1, 'qty' => 500],
        ];
        foreach ($stockMap as $productId => $stock) {
            ProductStock::create([
                'product_id' => $productId,
                'warehouse_id' => $stock['warehouse'],
                'quantity' => $stock['qty'],
            ]);
        }

        $movements = [
            [1, 'in', 50000, 'Production batch completion'],
            [2, 'in', 30000, 'Production batch completion'],
            [3, 'in', 12000, 'Production batch completion'],
            [4, 'in', 40000, 'Production batch completion'],
            [1, 'out', 20000, 'Sales order SO-000001'],
            [2, 'out', 15000, 'Sales order SO-000002'],
            [3, 'out', 5000, 'Sales order SO-000003'],
            [4, 'out', 20000, 'Sales order SO-000004'],
            [1, 'out', 30000, 'Sales order SO-000005'],
            [2, 'out', 10000, 'Sales order SO-000006'],
        ];
        foreach ($movements as [$productId, $type, $qty, $reference]) {
            StockMovement::create([
                'typeable_type' => 'App\\Models\\Product',
                'typeable_id' => $productId,
                'movement_type' => $type,
                'quantity' => $qty,
                'reference' => $reference,
                'notes' => 'Demo stock movement',
                'created_by' => $adminId,
            ]);
        }

        // ------------------------------------------------------------------
        // Settings
        // ------------------------------------------------------------------
        $settings = [
            ['key' => 'company_name', 'value' => 'Bricks Factory Ltd', 'group' => 'general'],
            ['key' => 'company_address', 'value' => 'Gazaria, Munshiganj', 'group' => 'general'],
            ['key' => 'company_phone', 'value' => '01700-000000', 'group' => 'general'],
            ['key' => 'company_email', 'value' => 'info@bricksfactory.com', 'group' => 'general'],
            ['key' => 'currency', 'value' => 'BDT', 'group' => 'general'],
            ['key' => 'vat_rate', 'value' => '5', 'group' => 'invoice'],
            ['key' => 'invoice_prefix', 'value' => 'INV-', 'group' => 'invoice'],
            ['key' => 'production_target_notice', 'value' => '30', 'group' => 'production'],
        ];
        foreach ($settings as $s) {
            Setting::create($s);
        }
    }
}
