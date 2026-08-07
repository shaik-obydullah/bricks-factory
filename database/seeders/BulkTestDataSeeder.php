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
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\QualityCheck;
use App\Models\QualityCheckItem;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\StockMovement;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class BulkTestDataSeeder extends Seeder
{
    protected int $adminId = 1;

    protected array $firstNames = [
        'Mohammad', 'Ahmed', 'Abdullah', 'Hasan', 'Sabbir', 'Rakib', 'Tanvir', 'Mehedi', 'Sohel', 'Rashed',
        'Imran', 'Arif', 'Sajid', 'Nayeem', 'Rifat', 'Farhan', 'Jubayer', 'Sakib', 'Mahin', 'Shakil',
        'Nadim', 'Riyad', 'Saif', 'Tanim', 'Akash', 'Rony', 'Bipul', 'Sumon', 'Alamin', 'Rubel',
        'Tuhin', 'Rana', 'Emon', 'Sharif', 'Mamun', 'Habib', 'Kawsar', 'Roni', 'Shihab', 'Masud',
        'Arafat', 'Forhad', 'Galib', 'Hridoy', 'Iqbal', 'Jahid', 'Kamruzzaman', 'Liton', 'Milon', 'Nazmul',
        'Omar', 'Parvez', 'Rahim', 'Sajal', 'Tareq', 'Uzzal', 'Vobon', 'Wahid', 'Yeasin', 'Zahid',
    ];

    protected array $lastNames = [
        'Hossain', 'Rahman', 'Islam', 'Ahmed', 'Mia', 'Khan', 'Sarker', 'Chowdhury', 'Uddin', 'Mondol',
        'Sikder', 'Pramanik', 'Sheikh', 'Mollah', 'Biswas', 'Das', 'Dey', 'Pal', 'Ghosh', 'Saha',
        'Haldar', 'Sen', 'Roy', 'Karmakar', 'Barman', 'Majumder', 'Talukder', 'Howlader', 'Gazi', 'Fakir',
    ];

    protected array $companyNames = [
        'Builders', 'Construction', 'Developers', 'Real Estate', 'Housing', 'Traders', 'Enterprises', 'Limited',
        'Associates', 'Projects', 'Infrastructure', 'Engineers', 'Contractors', 'Materials', 'Suppliers',
    ];

    protected array $cityNames = [
        'Dhaka', 'Chattogram', 'Rajshahi', 'Khulna', 'Sylhet', 'Barishal', 'Rangpur', 'Mymensingh', 'Cumilla', 'Gazipur',
    ];

    public function run(): void
    {
        $this->adminId = \App\Models\User::query()->value('id') ?? 1;
        $today = Carbon::today();
        $now = now();

        $this->cleanup();

        $this->seedCustomers();
        $this->seedSuppliers();
        $employees = $this->seedEmployees();
        $this->seedAttendance($employees, $today);

        $batches = $this->seedProductionOrders($today, $now);
        $this->seedSales($today, $now);
        $this->seedPurchases($today, $now);
        $this->seedQualityChecks($batches, $now);
        $this->seedStockMovements($now);

        $this->command?->info('Bulk test data seeded successfully.');
    }

    protected function cleanup(): void
    {
        DB::table('stock_movements')->where('reference', 'like', 'BULK-MOV-%')->delete();
        DB::table('purchase_orders')->where('order_number', 'like', 'BULK-PR-%')->delete();
        DB::table('sales_orders')->where('order_number', 'like', 'BULK-SO-%')->delete();
        DB::table('production_orders')->where('order_number', 'like', 'BULK-PO-%')->delete();
        DB::table('employees')->where('employee_id', 'like', 'BEMP-%')->delete();
        DB::table('employees')->where('employee_id', 'like', 'EMP-01%')->delete();
        DB::table('suppliers')->where('email', 'like', 'supplier%@example.com')->delete();
        DB::table('customers')->where('email', 'like', 'customer%@example.com')->delete();
    }

    protected function seedCustomers(): void
    {
        $rows = [];
        $now = now();
        for ($i = 0; $i < 500; $i++) {
            $rows[] = [
                'name' => $this->firstNames[array_rand($this->firstNames)] . ' ' . $this->lastNames[array_rand($this->lastNames)],
                'company' => $this->firstNames[array_rand($this->firstNames)] . ' ' . $this->companyNames[array_rand($this->companyNames)],
                'phone' => '01' . random_int(0, 9) . rand(10000000, 99999999),
                'email' => 'customer' . ($i + 1) . '@example.com',
                'address' => 'House ' . rand(1, 500) . ', Road ' . rand(1, 30) . ', ',
                'city' => $this->cityNames[array_rand($this->cityNames)],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            Customer::insert($chunk);
        }
    }

    protected function seedSuppliers(): void
    {
        $materials = ['Clay', 'Sand', 'Cement', 'Fly Ash', 'Water', 'Fuel', 'Chemicals', 'Packaging', 'Tiles', 'Aggregates'];
        $rows = [];
        $now = now();
        for ($i = 0; $i < 150; $i++) {
            $rows[] = [
                'name' => $materials[array_rand($materials)] . ' Supply Co ' . ($i + 1),
                'company' => $this->companyNames[array_rand($this->companyNames)] . ' Ltd',
                'phone' => '01' . random_int(0, 9) . rand(10000000, 99999999),
                'email' => 'supplier' . ($i + 1) . '@example.com',
                'address' => 'Plot ' . rand(1, 999) . ', Industrial Area',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            Supplier::insert($chunk);
        }
    }

    protected function seedEmployees(): array
    {
        $departments = ['Production', 'Quality Control', 'Logistics', 'Inventory', 'Maintenance', 'HR', 'Finance'];
        $designations = ['Machine Operator', 'Kiln Operator', 'Dryer Operator', 'Loader', 'QC Inspector', 'Store Keeper', 'Driver', 'Electrician', 'Mould Worker', 'Supervisor', 'Accountant', 'Helper'];
        $employeeIds = [];
        $rows = [];
        $now = now();
        for ($i = 0; $i < 200; $i++) {
            $join = Carbon::today()->subMonths(rand(3, 48));
            $rows[] = [
                'employee_id' => 'BEMP-' . str_pad((string) ($i + 1), 5, '0', STR_PAD_LEFT),
                'name' => $this->firstNames[array_rand($this->firstNames)] . ' ' . $this->lastNames[array_rand($this->lastNames)],
                'phone' => '01' . random_int(0, 9) . rand(10000000, 99999999),
                'email' => 'emp' . ($i + 1) . '@bricks.com',
                'designation' => $designations[array_rand($designations)],
                'department' => $departments[array_rand($departments)],
                'shift_id' => rand(1, 3),
                'joining_date' => $join->toDateString(),
                'status' => (rand(1, 20) === 1) ? 'inactive' : 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            $created = Employee::insert($chunk);
            $start = Employee::max('id') - count($chunk) + 1;
            foreach (range($start, $start + count($chunk) - 1) as $id) {
                $employeeIds[] = $id;
            }
        }

        return $employeeIds;
    }

    protected function seedAttendance(array $employeeIds, Carbon $today): void
    {
        $rows = [];
        $now = now();
        $total = 0;
        for ($d = 1; $d <= 120; $d++) {
            $date = $today->copy()->subDays($d);
            if ($date->isFriday()) {
                continue;
            }
            foreach ($employeeIds as $empId) {
                $shift = ($empId % 3) + 1;
                $shiftStart = $shift === 3 ? '22:00' : ($shift === 2 ? '14:00' : '06:00');
                $shiftEnd = $shift === 3 ? '06:00' : ($shift === 2 ? '22:00' : '14:00');
                $absent = ($empId + $d) % 17 === 0;
                $rows[] = [
                    'employee_id' => $empId,
                    'date' => $date->toDateString(),
                    'check_in' => $absent ? null : $shiftStart,
                    'check_out' => $absent ? null : $shiftEnd,
                    'status' => $absent ? 'absent' : 'present',
                    'notes' => $absent ? 'Leave' : null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $total++;
                if ($total % 1000 === 0) {
                    Attendance::insert($rows);
                    $rows = [];
                }
            }
        }
        if ($rows) {
            Attendance::insert($rows);
        }
    }

    protected function seedProductionOrders(Carbon $today, $now): array
    {
        $batchIds = [];
        $statuses = ['completed', 'completed', 'completed', 'in_progress', 'confirmed', 'draft'];
        $priorities = ['high', 'medium', 'low'];
        $lastOrderId = ProductionOrder::max('id') ?? 0;
        $rows = [];
        $orderCounter = 0;
        $chunkCount = 0;

        for ($i = 0; $i < 1500; $i++) {
            $orderCounter++;
            $chunkCount++;
            $productId = rand(1, 5);
            $daysAgo = rand(0, 180);
            $status = $statuses[array_rand($statuses)];
            $qty = rand(5000, 60000);
            $rows[] = [
                'order_number' => 'BULK-PO-' . str_pad((string) ($lastOrderId + $orderCounter), 6, '0', STR_PAD_LEFT),
                'product_id' => $productId,
                'quantity' => $qty,
                'planned_date' => $today->copy()->subDays($daysAgo)->toDateString(),
                'status' => $status,
                'priority' => $priorities[array_rand($priorities)],
                'notes' => 'Bulk test production order',
                'created_by' => $this->adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if ($chunkCount >= 500) {
                $rows = $this->flushProductionOrders($rows, $batchIds, $now, $today);
                $chunkCount = 0;
            }
        }
        if ($rows) {
            $this->flushProductionOrders($rows, $batchIds, $now, $today);
        }

        return $batchIds;
    }

    protected function flushProductionOrders(array &$rows, array &$batchIds, $now, Carbon $today): array
    {
        ProductionOrder::insert($rows);
        $start = ProductionOrder::max('id') - count($rows) + 1;
        $batchRows = [];
        $batchCount = 0;
        foreach (array_keys($rows) as $i) {
            $orderId = $start + $i;
            $status = $rows[$i]['status'];
            $qty = (int) $rows[$i]['quantity'];
            $done = match ($status) {
                'completed' => $qty,
                'in_progress' => (int) ($qty * 0.6),
                default => 0,
            };
            $batches = max(1, (int) round($done / 25000));
            $perBatch = $batches > 0 ? (int) floor($done / $batches) : 0;
            for ($b = 0; $b < $batches; $b++) {
                $produced = ($b === $batches - 1) ? $done - ($perBatch * $b) : $perBatch;
                if ($produced <= 0) {
                    continue;
                }
                $rejected = (int) round($produced * 0.015);
                $startTime = Carbon::parse($rows[$i]['planned_date'])->setTime(6, 0);
                $completedBatch = $status === 'completed';
                $batchRows[] = [
                    'order_id' => $orderId,
                    'shift_id' => (($orderId + $b) % 3) + 1,
                    'machine_id' => (($orderId + $b) % 5) + 1,
                    'quantity_produced' => $produced,
                    'quantity_rejected' => $rejected,
                    'start_time' => $startTime,
                    'end_time' => $completedBatch ? $startTime->copy()->addHours(8) : null,
                    'status' => $completedBatch ? 'completed' : 'running',
                    'operator_id' => $this->adminId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $batchCount++;
                if ($batchCount % 500 === 0) {
                    $this->flushBatches($batchRows, $batchIds, $now);
                }
            }
        }
        $this->flushBatches($batchRows, $batchIds, $now);

        return [];
    }

    protected function flushBatches(array &$batchRows, array &$batchIds, $now): void
    {
        if (!$batchRows) {
            return;
        }
        ProductionBatch::insert($batchRows);
        $start = ProductionBatch::max('id') - count($batchRows) + 1;
        foreach (range($start, $start + count($batchRows) - 1) as $id) {
            $batchIds[] = $id;
        }
        $batchRows = [];
    }

    protected function seedSales(Carbon $today, $now): void
    {
        $customerIds = Customer::query()->pluck('id')->all();
        $customerCount = count($customerIds);
        $statuses = ['completed', 'completed', 'in_progress', 'confirmed', 'draft'];
        $products = [
            1 => 9.50, 2 => 12.00, 3 => 22.00, 4 => 7.25, 5 => 18.00,
        ];
        $methods = ['bkash', 'nagad', 'bank', 'cash'];
        $vehicles = ['Dhaka Metro-11-4455', 'Chattogram-12-6677', 'Dhaka Metro-15-8899', 'Rajshahi-20-1122', 'Sylhet-16-3344'];
        $drivers = ['Kamal Hossain', 'Rahim Uddin', 'Farhan Ahmed', 'Sumon Mia', 'Jahid Hasan'];

        $lastSoId = SalesOrder::max('id') ?? 0;
        $lastInvId = Invoice::max('id') ?? 0;
        $orderRows = [];
        $orderCounter = 0;

        for ($i = 0; $i < 2000; $i++) {
            $orderCounter++;
            $daysAgo = rand(0, 180);
            $status = $statuses[array_rand($statuses)];
            $deliveryDaysAgo = in_array($status, ['completed', 'in_progress']) ? max(0, $daysAgo - rand(1, 5)) : null;
            $orderRows[] = [
                'order_number' => 'BULK-SO-' . str_pad((string) ($lastSoId + $orderCounter), 6, '0', STR_PAD_LEFT),
                'customer_id' => $customerIds[array_rand($customerIds)],
                'order_date' => $today->copy()->subDays($daysAgo)->toDateString(),
                'delivery_date' => $deliveryDaysAgo !== null ? $today->copy()->subDays($deliveryDaysAgo)->toDateString() : null,
                'status' => $status,
                'notes' => 'Bulk test sales order',
                'created_by' => $this->adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        SalesOrder::insert($orderRows);
        $orderStart = SalesOrder::max('id') - count($orderRows) + 1;

        $itemRows = [];
        $itemCount = 0;
        $invoiceRows = [];
        $paymentRows = [];
        $deliveryRows = [];

        foreach ($orderRows as $idx => $order) {
            $orderId = $orderStart + $idx;
            $subtotal = 0;
            $numItems = rand(1, 3);
            $chosen = [];
            for ($k = 0; $k < $numItems; $k++) {
                $productId = array_rand($products);
                if (isset($chosen[$productId])) {
                    continue;
                }
                $chosen[$productId] = true;
                $qty = rand(500, 30000);
                $price = $products[$productId];
                $total = round($qty * $price, 2);
                $subtotal += $total;
                $itemRows[] = [
                    'order_id' => $orderId,
                    'product_id' => $productId,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total' => $total,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $itemCount++;
            }

            $discount = round($subtotal * 0.02, 2);
            $tax = round(($subtotal - $discount) * 0.05, 2);
            $final = round($subtotal - $discount + $tax, 2);

            SalesOrder::where('id', $orderId)->update([
                'total_amount' => $subtotal,
                'discount' => $discount,
                'tax' => $tax,
                'final_amount' => $final,
            ]);

            if (in_array($order['status'], ['completed', 'in_progress'])) {
                $daysAgo = (int) Carbon::parse($order['order_date'])->diffInDays(Carbon::today());
                $invoiceRows[] = [
                    'order_id' => $orderId,
                    'invoice_number' => 'BULK-INV-' . str_pad((string) ($lastInvId + count($invoiceRows) + 1), 6, '0', STR_PAD_LEFT),
                    'invoice_date' => $order['order_date'],
                    'due_date' => $today->copy()->subDays(max(0, $daysAgo - 15))->toDateString(),
                    'amount' => $final,
                    'paid_amount' => $order['status'] === 'completed' ? $final : round($final * 0.5, 2),
                    'status' => $order['status'] === 'completed' ? 'paid' : 'partial',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($order['delivery_date'] !== null) {
                $deliveryRows[] = [
                    'order_id' => $orderId,
                    'delivery_date' => $order['delivery_date'],
                    'status' => 'delivered',
                    'vehicle_number' => $vehicles[array_rand($vehicles)],
                    'driver_name' => $drivers[array_rand($drivers)],
                    'notes' => 'Bulk test delivery',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($itemCount >= 500) {
                SalesOrderItem::insert($itemRows);
                $itemRows = [];
                $itemCount = 0;
            }
        }
        if ($itemRows) {
            SalesOrderItem::insert($itemRows);
        }

        Invoice::insert($invoiceRows);
        $invoiceStart = Invoice::max('id') - count($invoiceRows) + 1;
        foreach ($invoiceRows as $idx => $inv) {
            $daysAgo = (int) Carbon::parse($inv['invoice_date'])->diffInDays(Carbon::today());
            $paymentRows[] = [
                'invoice_id' => $invoiceStart + $idx,
                'payment_date' => $today->copy()->subDays(max(0, $daysAgo - 5))->toDateString(),
                'amount' => $inv['paid_amount'],
                'method' => $methods[array_rand($methods)],
                'reference' => 'TXN-' . random_int(100000, 999999),
                'notes' => 'Bulk test payment',
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        Payment::insert($paymentRows);
        Delivery::insert($deliveryRows);
    }

    protected function seedPurchases(Carbon $today, $now): void
    {
        $supplierIds = Supplier::query()->pluck('id')->all();
        $supplierCount = count($supplierIds);
        $statuses = ['completed', 'completed', 'confirmed', 'draft'];
        $materials = [1 => 1800.00, 2 => 950.00, 3 => 520.00, 4 => 1200.00, 5 => 15.00];

        $lastPoId = PurchaseOrder::max('id') ?? 0;
        $lastPiId = PurchaseInvoice::max('id') ?? 0;
        $orderRows = [];
        $orderCounter = 0;

        for ($i = 0; $i < 800; $i++) {
            $orderCounter++;
            $daysAgo = rand(0, 180);
            $status = $statuses[array_rand($statuses)];
            $expected = $today->copy()->subDays(max(0, $daysAgo - rand(1, 5)))->toDateString();
            $orderRows[] = [
                'order_number' => 'BULK-PR-' . str_pad((string) ($lastPoId + $orderCounter), 6, '0', STR_PAD_LEFT),
                'supplier_id' => $supplierIds[array_rand($supplierIds)],
                'order_date' => $today->copy()->subDays($daysAgo)->toDateString(),
                'expected_date' => $expected,
                'status' => $status,
                'notes' => 'Bulk test purchase order',
                'created_by' => $this->adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        PurchaseOrder::insert($orderRows);
        $orderStart = PurchaseOrder::max('id') - count($orderRows) + 1;

        $itemRows = [];
        $itemCount = 0;
        $receiptRows = [];
        $invoiceRows = [];

        foreach ($orderRows as $idx => $order) {
            $orderId = $orderStart + $idx;
            $total = 0;
            $numItems = rand(1, 3);
            for ($k = 0; $k < $numItems; $k++) {
                $materialId = array_rand($materials);
                $qty = rand(50, 500);
                $price = $materials[$materialId];
                $lineTotal = round($qty * $price, 2);
                $total += $lineTotal;
                $itemRows[] = [
                    'order_id' => $orderId,
                    'material_id' => $materialId,
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total' => $lineTotal,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $itemCount++;
            }

            PurchaseOrder::where('id', $orderId)->update(['total_amount' => $total]);

            if ($order['status'] === 'completed') {
                $receiptRows[] = [
                    'order_id' => $orderId,
                    'receipt_date' => $order['expected_date'],
                    'received_by' => $this->adminId,
                    'status' => 'received',
                    'notes' => 'Bulk goods receipt',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $invoiceRows[] = [
                    'order_id' => $orderId,
                    'invoice_number' => 'BULK-PI-' . str_pad((string) ($lastPiId + count($invoiceRows) + 1), 6, '0', STR_PAD_LEFT),
                    'invoice_date' => $order['expected_date'],
                    'amount' => $total,
                    'paid_amount' => $total,
                    'status' => 'paid',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            } elseif ($order['status'] === 'confirmed') {
                $invoiceRows[] = [
                    'order_id' => $orderId,
                    'invoice_number' => 'BULK-PI-' . str_pad((string) ($lastPiId + count($invoiceRows) + 1), 6, '0', STR_PAD_LEFT),
                    'invoice_date' => $order['expected_date'],
                    'amount' => $total,
                    'paid_amount' => 0,
                    'status' => 'pending',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if ($itemCount >= 500) {
                PurchaseOrderItem::insert($itemRows);
                $itemRows = [];
                $itemCount = 0;
            }
        }
        if ($itemRows) {
            PurchaseOrderItem::insert($itemRows);
        }
        GoodsReceipt::insert($receiptRows);
        PurchaseInvoice::insert($invoiceRows);
    }

    protected function seedQualityChecks(array $batchIds, $now): void
    {
        $checkRows = [];
        $checkItemRows = [];
        $defectRows = [];
        $inspectors = \App\Models\User::query()->pluck('id')->all();
        if (!$inspectors) {
            $inspectors = [$this->adminId];
        }

        $nextCheckId = (QualityCheck::max('id') ?? 0) + 1;
        $batches = ProductionBatch::whereIn('id', $batchIds)->where('status', 'completed')->get(['id', 'start_time', 'quantity_rejected']);
        foreach ($batches as $idx => $batch) {
            if ($idx % 3 !== 0) {
                continue;
            }
            $checkId = $nextCheckId + count($checkRows);
            $checkRows[] = [
                'batch_id' => $batch->id,
                'check_date' => $batch->start_time ? Carbon::parse($batch->start_time)->toDateString() : Carbon::today()->toDateString(),
                'inspector_id' => $inspectors[array_rand($inspectors)],
                'status' => 'passed',
                'notes' => 'Bulk test quality check',
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $dimensionOk = ($batch->id % 5) !== 0;
            $checkItemRows[] = [
                'check_id' => $checkId, 'parameter' => 'Compressive Strength', 'expected_value' => '>= 10 MPa', 'actual_value' => round(10.5 + ($batch->id % 5), 1) . ' MPa', 'status' => 'passed',
                'created_at' => $now, 'updated_at' => $now,
            ];
            $checkItemRows[] = [
                'check_id' => $checkId, 'parameter' => 'Dimensions', 'expected_value' => '240x115x70 mm', 'actual_value' => $dimensionOk ? '240x115x70 mm' : '238x115x70 mm', 'status' => $dimensionOk ? 'passed' : 'failed',
                'created_at' => $now, 'updated_at' => $now,
            ];
            $checkItemRows[] = [
                'check_id' => $checkId, 'parameter' => 'Water Absorption', 'expected_value' => '<= 15%', 'actual_value' => round(12 + ($batch->id % 3), 1) . '%', 'status' => 'passed',
                'created_at' => $now, 'updated_at' => $now,
            ];
            if ($batch->quantity_rejected > 0) {
                $defectRows[] = [
                    'batch_id' => $batch->id,
                    'type' => 'Cracked surface',
                    'severity' => 'medium',
                    'description' => round($batch->quantity_rejected * 0.5) . ' pcs rejected for surface cracks.',
                    'status' => 'resolved',
                    'resolved_by' => $this->adminId,
                    'resolved_at' => Carbon::parse($batch->start_time)->addDay(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        foreach (array_chunk($checkRows, 500) as $chunk) {
            QualityCheck::insert($chunk);
        }
        foreach (array_chunk($checkItemRows, 500) as $chunk) {
            QualityCheckItem::insert($chunk);
        }
        foreach (array_chunk($defectRows, 500) as $chunk) {
            Defect::insert($chunk);
        }
    }

    protected function seedStockMovements($now): void
    {
        $productCount = \App\Models\Product::count();
        $rows = [];
        for ($i = 0; $i < 3000; $i++) {
            $type = rand(0, 1) === 0 ? 'in' : 'out';
            $rows[] = [
                'typeable_type' => 'App\\Models\\Product',
                'typeable_id' => rand(1, $productCount),
                'movement_type' => $type,
                'quantity' => rand(500, 50000),
                'reference' => 'BULK-MOV-' . ($i + 1),
                'notes' => 'Bulk test stock movement',
                'created_by' => $this->adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        foreach (array_chunk($rows, 1000) as $chunk) {
            StockMovement::insert($chunk);
        }
    }
}
