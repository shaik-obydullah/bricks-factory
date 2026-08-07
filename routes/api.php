<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\MachineController;
use App\Http\Controllers\Api\ProductionController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\QualityController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\SalesController;
use App\Http\Controllers\Api\SettingsController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\WarehouseController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Production
    Route::get('production/orders', [ProductionController::class, 'index']);
    Route::post('production/orders', [ProductionController::class, 'store']);
    Route::get('production/orders/{productionOrder}', [ProductionController::class, 'show']);
    Route::put('production/orders/{productionOrder}', [ProductionController::class, 'update']);
    Route::delete('production/orders/{productionOrder}', [ProductionController::class, 'destroy']);
    Route::get('production/orders/{productionOrder}/batches', [ProductionController::class, 'batches']);
    Route::post('production/batches/start', [ProductionController::class, 'startBatch']);
    Route::put('production/batches/{productionBatch}/complete', [ProductionController::class, 'completeBatch']);
    Route::get('machines', [ProductionController::class, 'machines']);
    Route::post('machines', [ProductionController::class, 'storeMachine']);
    Route::get('production/targets', [ProductionController::class, 'targets']);
    Route::post('production/targets', [ProductionController::class, 'storeTarget']);

    // Production (kebab-case paths used by the SPA)
    Route::get('production-orders', [ProductionController::class, 'index']);
    Route::post('production-orders', [ProductionController::class, 'store']);
    Route::get('production-orders/{productionOrder}', [ProductionController::class, 'show']);
    Route::put('production-orders/{productionOrder}', [ProductionController::class, 'update']);
    Route::delete('production-orders/{productionOrder}', [ProductionController::class, 'destroy']);
    Route::get('production-batches', [ProductionController::class, 'batchesIndex']);
    Route::post('production-batches', [ProductionController::class, 'startBatch']);
    Route::put('production-batches/{productionBatch}/complete', [ProductionController::class, 'completeBatch']);
    Route::patch('production-batches/{productionBatch}/complete', [ProductionController::class, 'completeBatch']);
    Route::get('production-targets', [ProductionController::class, 'targets']);
    Route::post('production-targets', [ProductionController::class, 'storeTarget']);
    Route::put('production-targets/{productionTarget}', [ProductionController::class, 'updateTarget']);
    Route::delete('production-targets/{productionTarget}', [ProductionController::class, 'destroyTarget']);

    // Inventory
    Route::get('products', [InventoryController::class, 'products']);
    Route::post('products', [InventoryController::class, 'storeProduct']);
    Route::get('products/{product}', [InventoryController::class, 'showProduct']);
    Route::put('products/{product}', [InventoryController::class, 'updateProduct']);
    Route::delete('products/{product}', [InventoryController::class, 'destroyProduct']);
    Route::get('raw-materials', [InventoryController::class, 'rawMaterials']);
    Route::post('raw-materials', [InventoryController::class, 'storeRawMaterial']);
    Route::get('stock-movements', [InventoryController::class, 'stockMovements']);
    Route::post('stock-movements', [InventoryController::class, 'updateStock']);
    Route::get('stock-alerts', [InventoryController::class, 'stockAlerts']);

    // Categories, Warehouses, Units
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('warehouses', WarehouseController::class);
    Route::apiResource('units', UnitController::class);

    // Quality
    Route::get('quality/checks', [QualityController::class, 'checks']);
    Route::post('quality/checks', [QualityController::class, 'storeCheck']);
    Route::get('quality/checks/{qualityCheck}', [QualityController::class, 'showCheck']);
    Route::put('quality/check-items/{qualityCheckItem}', [QualityController::class, 'updateCheckItem']);
    Route::get('quality/defects', [QualityController::class, 'defects']);
    Route::post('quality/defects', [QualityController::class, 'storeDefect']);
    Route::put('quality/defects/{defect}/resolve', [QualityController::class, 'resolveDefect']);

    // Quality (kebab-case paths used by the SPA)
    Route::get('quality-checks', [QualityController::class, 'checks']);
    Route::post('quality-checks', [QualityController::class, 'storeCheck']);
    Route::get('quality-checks/{qualityCheck}', [QualityController::class, 'showCheck']);
    Route::delete('quality-checks/{qualityCheck}', [QualityController::class, 'destroyCheck']);
    Route::get('defects', [QualityController::class, 'defects']);
    Route::post('defects', [QualityController::class, 'storeDefect']);
    Route::put('defects/{defect}', [QualityController::class, 'updateDefect']);
    Route::delete('defects/{defect}', [QualityController::class, 'destroyDefect']);

    // Sales
    Route::get('customers', [SalesController::class, 'customers']);
    Route::post('customers', [SalesController::class, 'storeCustomer']);
    Route::get('customers/{customer}', [SalesController::class, 'showCustomer']);
    Route::put('customers/{customer}', [SalesController::class, 'updateCustomer']);
    Route::delete('customers/{customer}', [SalesController::class, 'destroyCustomer']);
    Route::get('sales/orders', [SalesController::class, 'orders']);
    Route::post('sales/orders', [SalesController::class, 'storeOrder']);
    Route::get('sales/orders/{salesOrder}', [SalesController::class, 'showOrder']);
    Route::get('sales/invoices', [SalesController::class, 'invoices']);
    Route::post('sales/orders/{salesOrder}/invoice', [SalesController::class, 'generateInvoice']);
    Route::post('sales/invoices/{invoice}/pay', [SalesController::class, 'processPayment']);

    // Sales (kebab-case paths used by the SPA)
    Route::get('sales-orders', [SalesController::class, 'orders']);
    Route::post('sales-orders', [SalesController::class, 'storeOrder']);
    Route::get('sales-orders/{salesOrder}', [SalesController::class, 'showOrder']);
    Route::put('sales-orders/{salesOrder}', [SalesController::class, 'updateOrder']);
    Route::delete('sales-orders/{salesOrder}', [SalesController::class, 'destroyOrder']);
    Route::get('invoices', [SalesController::class, 'invoices']);
    Route::post('invoices', [SalesController::class, 'storeInvoice']);
    Route::put('invoices/{invoice}', [SalesController::class, 'updateInvoice']);
    Route::delete('invoices/{invoice}', [SalesController::class, 'destroyInvoice']);
    Route::get('payments', [SalesController::class, 'payments']);
    Route::post('payments', [SalesController::class, 'storePayment']);
    Route::delete('payments/{payment}', [SalesController::class, 'destroyPayment']);

    // Purchase
    Route::get('suppliers', [PurchaseController::class, 'suppliers']);
    Route::post('suppliers', [PurchaseController::class, 'storeSupplier']);
    Route::get('suppliers/{supplier}', [PurchaseController::class, 'showSupplier']);
    Route::put('suppliers/{supplier}', [PurchaseController::class, 'updateSupplier']);
    Route::delete('suppliers/{supplier}', [PurchaseController::class, 'destroySupplier']);
    Route::get('purchase/orders', [PurchaseController::class, 'orders']);
    Route::post('purchase/orders', [PurchaseController::class, 'storeOrder']);
    Route::get('purchase/orders/{purchaseOrder}', [PurchaseController::class, 'showOrder']);
    Route::post('purchase/orders/{purchaseOrder}/receive', [PurchaseController::class, 'receiveGoods']);

    // Purchase (kebab-case paths used by the SPA)
    Route::get('purchase-orders', [PurchaseController::class, 'orders']);
    Route::post('purchase-orders', [PurchaseController::class, 'storeOrder']);
    Route::get('purchase-orders/{purchaseOrder}', [PurchaseController::class, 'showOrder']);
    Route::put('purchase-orders/{purchaseOrder}', [PurchaseController::class, 'updateOrder']);
    Route::delete('purchase-orders/{purchaseOrder}', [PurchaseController::class, 'destroyOrder']);
    Route::get('goods-receipts', [PurchaseController::class, 'goodsReceipts']);
    Route::post('goods-receipts', [PurchaseController::class, 'storeGoodsReceipt']);

    // Employees
    Route::get('employees', [EmployeeController::class, 'employees']);
    Route::post('employees', [EmployeeController::class, 'storeEmployee']);
    Route::get('employees/{employee}', [EmployeeController::class, 'showEmployee']);
    Route::put('employees/{employee}', [EmployeeController::class, 'updateEmployee']);
    Route::delete('employees/{employee}', [EmployeeController::class, 'destroyEmployee']);
    Route::get('attendance', [EmployeeController::class, 'attendance']);
    Route::post('attendance', [EmployeeController::class, 'storeAttendance']);

    // Shifts
    Route::apiResource('shifts', ShiftController::class);
    Route::apiResource('machines', MachineController::class)->except(['index', 'store']);

    // Reports
    Route::get('reports/dashboard', [ReportController::class, 'dashboard']);
    Route::get('reports/production', [ReportController::class, 'production']);
    Route::get('reports/inventory', [ReportController::class, 'inventory']);
    Route::get('reports/sales', [ReportController::class, 'sales']);
    Route::get('reports/quality', [ReportController::class, 'quality']);
    Route::get('reports/purchase', [ReportController::class, 'purchase']);
    Route::get('reports/export', [ReportController::class, 'export']);

    // Settings
    Route::get('settings', [SettingsController::class, 'index']);
    Route::get('settings/{group}', [SettingsController::class, 'get']);
    Route::post('settings', [SettingsController::class, 'set']);
    Route::put('settings/{setting}', [SettingsController::class, 'update']);
    Route::delete('settings/{setting}', [SettingsController::class, 'destroy']);
});
