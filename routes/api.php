<?php

use App\Http\Controllers\Api\AccessController;
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
    Route::get('production/orders', [ProductionController::class, 'index'])->middleware('permission:production.view');
    Route::post('production/orders', [ProductionController::class, 'store'])->middleware('permission:production.create');
    Route::get('production/orders/{productionOrder}', [ProductionController::class, 'show'])->middleware('permission:production.view');
    Route::put('production/orders/{productionOrder}', [ProductionController::class, 'update'])->middleware('permission:production.update');
    Route::delete('production/orders/{productionOrder}', [ProductionController::class, 'destroy'])->middleware('permission:production.delete');
    Route::get('production/orders/{productionOrder}/batches', [ProductionController::class, 'batches'])->middleware('permission:production.view');
    Route::post('production/batches/start', [ProductionController::class, 'startBatch'])->middleware('permission:production.create');
    Route::put('production/batches/{productionBatch}/complete', [ProductionController::class, 'completeBatch'])->middleware('permission:production.update');
    Route::get('machines', [ProductionController::class, 'machines'])->middleware('permission:production.view');
    Route::post('machines', [ProductionController::class, 'storeMachine'])->middleware('permission:production.create');
    Route::get('production/targets', [ProductionController::class, 'targets'])->middleware('permission:production.view');
    Route::post('production/targets', [ProductionController::class, 'storeTarget'])->middleware('permission:production.create');

    // Production (kebab-case paths used by the SPA)
    Route::get('production-orders', [ProductionController::class, 'index'])->middleware('permission:production.view');
    Route::post('production-orders', [ProductionController::class, 'store'])->middleware('permission:production.create');
    Route::get('production-orders/{productionOrder}', [ProductionController::class, 'show'])->middleware('permission:production.view');
    Route::put('production-orders/{productionOrder}', [ProductionController::class, 'update'])->middleware('permission:production.update');
    Route::delete('production-orders/{productionOrder}', [ProductionController::class, 'destroy'])->middleware('permission:production.delete');
    Route::get('production-batches', [ProductionController::class, 'batchesIndex'])->middleware('permission:production.view');
    Route::post('production-batches', [ProductionController::class, 'startBatch'])->middleware('permission:production.create');
    Route::put('production-batches/{productionBatch}/complete', [ProductionController::class, 'completeBatch'])->middleware('permission:production.update');
    Route::patch('production-batches/{productionBatch}/complete', [ProductionController::class, 'completeBatch'])->middleware('permission:production.update');
    Route::get('production-targets', [ProductionController::class, 'targets'])->middleware('permission:production.view');
    Route::post('production-targets', [ProductionController::class, 'storeTarget'])->middleware('permission:production.create');
    Route::put('production-targets/{productionTarget}', [ProductionController::class, 'updateTarget'])->middleware('permission:production.update');
    Route::delete('production-targets/{productionTarget}', [ProductionController::class, 'destroyTarget'])->middleware('permission:production.delete');

    // Inventory
    Route::get('products', [InventoryController::class, 'products'])->middleware('permission:inventory.view');
    Route::post('products', [InventoryController::class, 'storeProduct'])->middleware('permission:inventory.create');
    Route::get('products/{product}', [InventoryController::class, 'showProduct'])->middleware('permission:inventory.view');
    Route::put('products/{product}', [InventoryController::class, 'updateProduct'])->middleware('permission:inventory.update');
    Route::delete('products/{product}', [InventoryController::class, 'destroyProduct'])->middleware('permission:inventory.delete');
    Route::get('raw-materials', [InventoryController::class, 'rawMaterials'])->middleware('permission:inventory.view');
    Route::post('raw-materials', [InventoryController::class, 'storeRawMaterial'])->middleware('permission:inventory.create');
    Route::put('raw-materials/{rawMaterial}', [InventoryController::class, 'updateRawMaterial'])->middleware('permission:inventory.update');
    Route::delete('raw-materials/{rawMaterial}', [InventoryController::class, 'destroyRawMaterial'])->middleware('permission:inventory.delete');
    Route::get('stock-movements', [InventoryController::class, 'stockMovements'])->middleware('permission:inventory.view');
    Route::post('stock-movements', [InventoryController::class, 'updateStock'])->middleware('permission:inventory.create');
    Route::get('stock-alerts', [InventoryController::class, 'stockAlerts'])->middleware('permission:inventory.view');

    // Categories, Warehouses, Units
    Route::get('categories', [CategoryController::class, 'index'])->middleware('permission:inventory.view');
    Route::post('categories', [CategoryController::class, 'store'])->middleware('permission:inventory.create');
    Route::get('categories/{category}', [CategoryController::class, 'show'])->middleware('permission:inventory.view');
    Route::put('categories/{category}', [CategoryController::class, 'update'])->middleware('permission:inventory.update');
    Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->middleware('permission:inventory.delete');

    Route::get('warehouses', [WarehouseController::class, 'index'])->middleware('permission:inventory.view');
    Route::post('warehouses', [WarehouseController::class, 'store'])->middleware('permission:inventory.create');
    Route::get('warehouses/{warehouse}', [WarehouseController::class, 'show'])->middleware('permission:inventory.view');
    Route::put('warehouses/{warehouse}', [WarehouseController::class, 'update'])->middleware('permission:inventory.update');
    Route::delete('warehouses/{warehouse}', [WarehouseController::class, 'destroy'])->middleware('permission:inventory.delete');

    Route::get('units', [UnitController::class, 'index'])->middleware('permission:inventory.view');
    Route::post('units', [UnitController::class, 'store'])->middleware('permission:inventory.create');
    Route::get('units/{unit}', [UnitController::class, 'show'])->middleware('permission:inventory.view');
    Route::put('units/{unit}', [UnitController::class, 'update'])->middleware('permission:inventory.update');
    Route::delete('units/{unit}', [UnitController::class, 'destroy'])->middleware('permission:inventory.delete');

    // Quality
    Route::get('quality/checks', [QualityController::class, 'checks'])->middleware('permission:quality.view');
    Route::post('quality/checks', [QualityController::class, 'storeCheck'])->middleware('permission:quality.create');
    Route::get('quality/checks/{qualityCheck}', [QualityController::class, 'showCheck'])->middleware('permission:quality.view');
    Route::put('quality/check-items/{qualityCheckItem}', [QualityController::class, 'updateCheckItem'])->middleware('permission:quality.update');
    Route::get('quality/defects', [QualityController::class, 'defects'])->middleware('permission:quality.view');
    Route::post('quality/defects', [QualityController::class, 'storeDefect'])->middleware('permission:quality.create');
    Route::put('quality/defects/{defect}/resolve', [QualityController::class, 'resolveDefect'])->middleware('permission:quality.update');

    // Quality (kebab-case paths used by the SPA)
    Route::get('quality-checks', [QualityController::class, 'checks'])->middleware('permission:quality.view');
    Route::post('quality-checks', [QualityController::class, 'storeCheck'])->middleware('permission:quality.create');
    Route::get('quality-checks/{qualityCheck}', [QualityController::class, 'showCheck'])->middleware('permission:quality.view');
    Route::delete('quality-checks/{qualityCheck}', [QualityController::class, 'destroyCheck'])->middleware('permission:quality.delete');
    Route::get('defects', [QualityController::class, 'defects'])->middleware('permission:quality.view');
    Route::post('defects', [QualityController::class, 'storeDefect'])->middleware('permission:quality.create');
    Route::put('defects/{defect}', [QualityController::class, 'updateDefect'])->middleware('permission:quality.update');
    Route::delete('defects/{defect}', [QualityController::class, 'destroyDefect'])->middleware('permission:quality.delete');

    // Sales
    Route::get('customers', [SalesController::class, 'customers'])->middleware('permission:sales.view');
    Route::post('customers', [SalesController::class, 'storeCustomer'])->middleware('permission:sales.create');
    Route::get('customers/{customer}', [SalesController::class, 'showCustomer'])->middleware('permission:sales.view');
    Route::put('customers/{customer}', [SalesController::class, 'updateCustomer'])->middleware('permission:sales.update');
    Route::delete('customers/{customer}', [SalesController::class, 'destroyCustomer'])->middleware('permission:sales.delete');
    Route::get('sales/orders', [SalesController::class, 'orders'])->middleware('permission:sales.view');
    Route::post('sales/orders', [SalesController::class, 'storeOrder'])->middleware('permission:sales.create');
    Route::get('sales/orders/{salesOrder}', [SalesController::class, 'showOrder'])->middleware('permission:sales.view');
    Route::get('sales/invoices', [SalesController::class, 'invoices'])->middleware('permission:sales.view');
    Route::post('sales/orders/{salesOrder}/invoice', [SalesController::class, 'generateInvoice'])->middleware('permission:sales.create');
    Route::post('sales/invoices/{invoice}/pay', [SalesController::class, 'processPayment'])->middleware('permission:sales.update');

    // Sales (kebab-case paths used by the SPA)
    Route::get('sales-orders', [SalesController::class, 'orders'])->middleware('permission:sales.view');
    Route::post('sales-orders', [SalesController::class, 'storeOrder'])->middleware('permission:sales.create');
    Route::get('sales-orders/{salesOrder}', [SalesController::class, 'showOrder'])->middleware('permission:sales.view');
    Route::put('sales-orders/{salesOrder}', [SalesController::class, 'updateOrder'])->middleware('permission:sales.update');
    Route::delete('sales-orders/{salesOrder}', [SalesController::class, 'destroyOrder'])->middleware('permission:sales.delete');
    Route::get('invoices', [SalesController::class, 'invoices'])->middleware('permission:sales.view');
    Route::get('invoices/{invoice}', [SalesController::class, 'showInvoice'])->middleware('permission:sales.view');
    Route::post('invoices', [SalesController::class, 'storeInvoice'])->middleware('permission:sales.create');
    Route::put('invoices/{invoice}', [SalesController::class, 'updateInvoice'])->middleware('permission:sales.update');
    Route::delete('invoices/{invoice}', [SalesController::class, 'destroyInvoice'])->middleware('permission:sales.delete');
    Route::get('payments', [SalesController::class, 'payments'])->middleware('permission:sales.view');
    Route::post('payments', [SalesController::class, 'storePayment'])->middleware('permission:sales.create');
    Route::delete('payments/{payment}', [SalesController::class, 'destroyPayment'])->middleware('permission:sales.delete');

    // Purchase
    Route::get('suppliers', [PurchaseController::class, 'suppliers'])->middleware('permission:purchase.view');
    Route::post('suppliers', [PurchaseController::class, 'storeSupplier'])->middleware('permission:purchase.create');
    Route::get('suppliers/{supplier}', [PurchaseController::class, 'showSupplier'])->middleware('permission:purchase.view');
    Route::put('suppliers/{supplier}', [PurchaseController::class, 'updateSupplier'])->middleware('permission:purchase.update');
    Route::delete('suppliers/{supplier}', [PurchaseController::class, 'destroySupplier'])->middleware('permission:purchase.delete');
    Route::get('purchase/orders', [PurchaseController::class, 'orders'])->middleware('permission:purchase.view');
    Route::post('purchase/orders', [PurchaseController::class, 'storeOrder'])->middleware('permission:purchase.create');
    Route::get('purchase/orders/{purchaseOrder}', [PurchaseController::class, 'showOrder'])->middleware('permission:purchase.view');
    Route::post('purchase/orders/{purchaseOrder}/receive', [PurchaseController::class, 'receiveGoods'])->middleware('permission:purchase.update');

    // Purchase (kebab-case paths used by the SPA)
    Route::get('purchase-orders', [PurchaseController::class, 'orders'])->middleware('permission:purchase.view');
    Route::post('purchase-orders', [PurchaseController::class, 'storeOrder'])->middleware('permission:purchase.create');
    Route::get('purchase-orders/{purchaseOrder}', [PurchaseController::class, 'showOrder'])->middleware('permission:purchase.view');
    Route::put('purchase-orders/{purchaseOrder}', [PurchaseController::class, 'updateOrder'])->middleware('permission:purchase.update');
    Route::delete('purchase-orders/{purchaseOrder}', [PurchaseController::class, 'destroyOrder'])->middleware('permission:purchase.delete');
    Route::get('goods-receipts', [PurchaseController::class, 'goodsReceipts'])->middleware('permission:purchase.view');
    Route::post('goods-receipts', [PurchaseController::class, 'storeGoodsReceipt'])->middleware('permission:purchase.create');

    // Employees
    Route::get('employees', [EmployeeController::class, 'employees'])->middleware('permission:employees.view');
    Route::post('employees', [EmployeeController::class, 'storeEmployee'])->middleware('permission:employees.create');
    Route::get('employees/{employee}', [EmployeeController::class, 'showEmployee'])->middleware('permission:employees.view');
    Route::put('employees/{employee}', [EmployeeController::class, 'updateEmployee'])->middleware('permission:employees.update');
    Route::delete('employees/{employee}', [EmployeeController::class, 'destroyEmployee'])->middleware('permission:employees.delete');
    Route::get('attendance', [EmployeeController::class, 'attendance'])->middleware('permission:employees.view');
    Route::post('attendance', [EmployeeController::class, 'storeAttendance'])->middleware('permission:employees.create');

    // Shifts
    Route::get('shifts', [ShiftController::class, 'index'])->middleware('permission:production.view');
    Route::post('shifts', [ShiftController::class, 'store'])->middleware('permission:production.create');
    Route::get('shifts/{shift}', [ShiftController::class, 'show'])->middleware('permission:production.view');
    Route::put('shifts/{shift}', [ShiftController::class, 'update'])->middleware('permission:production.update');
    Route::delete('shifts/{shift}', [ShiftController::class, 'destroy'])->middleware('permission:production.delete');

    // Machines
    Route::get('machines/{machine}', [MachineController::class, 'show'])->middleware('permission:production.view');
    Route::put('machines/{machine}', [MachineController::class, 'update'])->middleware('permission:production.update');
    Route::delete('machines/{machine}', [MachineController::class, 'destroy'])->middleware('permission:production.delete');

    // Reports
    Route::get('reports/dashboard', [ReportController::class, 'dashboard'])->middleware('permission:reports.view');
    Route::get('reports/production', [ReportController::class, 'production'])->middleware('permission:reports.view');
    Route::get('reports/inventory', [ReportController::class, 'inventory'])->middleware('permission:reports.view');
    Route::get('reports/sales', [ReportController::class, 'sales'])->middleware('permission:reports.view');
    Route::get('reports/quality', [ReportController::class, 'quality'])->middleware('permission:reports.view');
    Route::get('reports/purchase', [ReportController::class, 'purchase'])->middleware('permission:reports.view');
    Route::get('reports/export', [ReportController::class, 'export'])->middleware('permission:reports.view');

    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->middleware('permission:settings.view');
    Route::get('settings/{group}', [SettingsController::class, 'get'])->middleware('permission:settings.view');
    Route::post('settings', [SettingsController::class, 'set'])->middleware('permission:settings.create');
    Route::put('settings/{setting}', [SettingsController::class, 'update'])->middleware('permission:settings.update');
    Route::delete('settings/{setting}', [SettingsController::class, 'destroy'])->middleware('permission:settings.delete');

    // Access management (users & roles)
    Route::get('users', [AccessController::class, 'users'])->middleware('permission:users.view');
    Route::post('users', [AccessController::class, 'storeUser'])->middleware('permission:users.create');
    Route::put('users/{user}', [AccessController::class, 'updateUser'])->middleware('permission:users.update');
    Route::delete('users/{user}', [AccessController::class, 'destroyUser'])->middleware('permission:users.delete');
    Route::get('roles', [AccessController::class, 'roles'])->middleware('permission:users.view');
    Route::post('roles', [AccessController::class, 'storeRole'])->middleware('permission:users.create');
    Route::put('roles/{role}', [AccessController::class, 'updateRole'])->middleware('permission:users.update');
    Route::delete('roles/{role}', [AccessController::class, 'destroyRole'])->middleware('permission:users.delete');
    Route::get('permissions', [AccessController::class, 'permissions'])->middleware('permission:users.view');
});
