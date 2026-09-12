<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\RawMaterial;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function updateStock($typeable, string $movementType, float $quantity, array $extra = []): StockMovement
    {
        $movement = DB::transaction(function () use ($typeable, $movementType, $quantity, $extra) {
            $movement = StockMovement::create([
                'typeable_type' => get_class($typeable),
                'typeable_id' => $typeable->id,
                'movement_type' => $movementType,
                'quantity' => $quantity,
                'reference' => $extra['reference'] ?? null,
                'notes' => $extra['notes'] ?? null,
                'created_by' => $extra['created_by'] ?? auth()->id(),
            ]);

            if ($typeable instanceof RawMaterial) {
                if ($movementType === 'in') {
                    $typeable->increment('current_stock', $quantity);
                } elseif ($movementType === 'out') {
                    $typeable->decrement('current_stock', $quantity);
                }
            }

            if ($typeable instanceof Product) {
                $this->adjustProductStock($typeable, $movementType, $quantity, $extra);
            }

            return $movement;
        });

        return $movement;
    }

    protected function adjustProductStock(Product $product, string $movementType, float $quantity, array $extra): void
    {
        $fromWarehouseId = $extra['from_warehouse_id'] ?? null;
        $toWarehouseId = $extra['to_warehouse_id'] ?? null;

        if ($movementType === 'transfer' && $fromWarehouseId && $toWarehouseId) {
            $this->stockFor($product, $fromWarehouseId)?->decrement('quantity', $quantity);
            $this->stockFor($product, $toWarehouseId)?->increment('quantity', $quantity);
            return;
        }

        $warehouseId = $fromWarehouseId ?: $toWarehouseId;
        if (!$warehouseId) {
            return;
        }

        $stock = $this->stockFor($product, $warehouseId);
        if ($movementType === 'in') {
            $stock->increment('quantity', $quantity);
        } elseif ($movementType === 'out') {
            $stock->decrement('quantity', $quantity);
        }
    }

    protected function stockFor(Product $product, int $warehouseId): ProductStock
    {
        return ProductStock::firstOrCreate(
            ['product_id' => $product->id, 'warehouse_id' => $warehouseId],
            ['quantity' => 0]
        );
    }

    public function getStockAlerts(): array
    {
        $materials = RawMaterial::where('current_stock', '<=', DB::raw('minimum_stock'))
            ->where('status', 'active')
            ->get();

        $outOfStockProducts = Product::where('status', 'active')
            ->whereHas('productStocks')
            ->whereDoesntHave('productStocks', fn ($q) => $q->where('quantity', '>', 0))
            ->get();

        $items = collect();

        foreach ($materials as $m) {
            $items->push([
                'id' => 'material-' . $m->id,
                'name' => $m->name,
                'type' => 'raw_material',
                'current_stock' => $m->current_stock,
                'minimum_stock' => $m->minimum_stock,
            ]);
        }

        foreach ($outOfStockProducts as $p) {
            $items->push([
                'id' => 'product-' . $p->id,
                'name' => $p->name,
                'type' => 'product',
                'current_stock' => 0,
                'minimum_stock' => null,
            ]);
        }

        return [
            'items' => $items->values(),
            'low_stock_materials' => $materials,
            'low_stock_products' => $outOfStockProducts,
            'count' => $items->count(),
        ];
    }

    public function transferStock(ProductStock $fromStock, ProductStock $toStock, float $quantity): void
    {
        DB::transaction(function () use ($fromStock, $toStock, $quantity) {
            $fromStock->decrement('quantity', $quantity);
            $toStock->increment('quantity', $quantity);
        });
    }

    public function getInventoryReport(array $filters = []): array
    {
        $products = Product::with('productStocks.warehouse')->where('status', 'active')->get();
        $materials = RawMaterial::where('status', 'active')->get();

        return [
            'products' => $products,
            'raw_materials' => $materials,
            'low_stock_alerts' => $this->getStockAlerts(),
        ];
    }
}
