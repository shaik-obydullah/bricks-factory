<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Machine;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Shift;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@bricks.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->call(RolePermissionSeeder::class);

        Category::insert([
            ['name' => 'Clay Bricks', 'type' => 'product', 'status' => 'active'],
            ['name' => 'Concrete Bricks', 'type' => 'product', 'status' => 'active'],
            ['name' => 'Fly Ash Bricks', 'type' => 'product', 'status' => 'active'],
        ]);

        Unit::insert([
            ['name' => 'Piece', 'symbol' => 'pcs', 'status' => 'active'],
            ['name' => 'Ton', 'symbol' => 't', 'status' => 'active'],
            ['name' => 'Kilogram', 'symbol' => 'kg', 'status' => 'active'],
            ['name' => 'Bag', 'symbol' => 'bag', 'status' => 'active'],
        ]);

        Shift::insert([
            ['name' => 'Morning', 'start_time' => '06:00:00', 'end_time' => '14:00:00', 'status' => 'active'],
            ['name' => 'Evening', 'start_time' => '14:00:00', 'end_time' => '22:00:00', 'status' => 'active'],
            ['name' => 'Night', 'start_time' => '22:00:00', 'end_time' => '06:00:00', 'status' => 'active'],
        ]);

        Machine::insert([
            ['name' => 'Extruder 1', 'type' => 'extruder', 'status' => 'active'],
            ['name' => 'Extruder 2', 'type' => 'extruder', 'status' => 'active'],
            ['name' => 'Kiln 1', 'type' => 'kiln', 'status' => 'active'],
            ['name' => 'Kiln 2', 'type' => 'kiln', 'status' => 'active'],
            ['name' => 'Dryer 1', 'type' => 'dryer', 'status' => 'active'],
        ]);

        Warehouse::insert([
            ['name' => 'Main Warehouse', 'location' => 'Site A', 'status' => 'active'],
            ['name' => 'Finished Goods', 'location' => 'Site A', 'status' => 'active'],
            ['name' => 'Raw Materials Yard', 'location' => 'Site B', 'status' => 'active'],
        ]);

        RawMaterial::insert([
            ['name' => 'Clay', 'code' => 'RAW-CLAY', 'unit' => 'ton', 'current_stock' => 500, 'minimum_stock' => 100, 'status' => 'active'],
            ['name' => 'Sand', 'code' => 'RAW-SAND', 'unit' => 'ton', 'current_stock' => 300, 'minimum_stock' => 50, 'status' => 'active'],
            ['name' => 'Cement', 'code' => 'RAW-CEMENT', 'unit' => 'kg', 'current_stock' => 10000, 'minimum_stock' => 1000, 'status' => 'active'],
            ['name' => 'Fly Ash', 'code' => 'RAW-FLYASH', 'unit' => 'ton', 'current_stock' => 200, 'minimum_stock' => 50, 'status' => 'active'],
            ['name' => 'Water', 'code' => 'RAW-WATER', 'unit' => 'liter', 'current_stock' => 5000, 'minimum_stock' => 1000, 'status' => 'active'],
        ]);

        Product::insert([
            ['name' => 'Standard Brick', 'code' => 'BRK-STD', 'type' => 'brick', 'category_id' => 1, 'unit' => 'pcs', 'status' => 'active'],
            ['name' => 'Hollow Brick', 'code' => 'BRK-HLW', 'type' => 'brick', 'category_id' => 1, 'unit' => 'pcs', 'status' => 'active'],
            ['name' => 'Solid Concrete Block', 'code' => 'BLK-SCB', 'type' => 'block', 'category_id' => 2, 'unit' => 'pcs', 'status' => 'active'],
            ['name' => 'Fly Ash Brick', 'code' => 'BRK-FAB', 'type' => 'brick', 'category_id' => 3, 'unit' => 'pcs', 'status' => 'active'],
            ['name' => 'Paver Block', 'code' => 'BLK-PVR', 'type' => 'block', 'category_id' => 2, 'unit' => 'pcs', 'status' => 'active'],
        ]);

        $this->call(DemoDataSeeder::class);
    }
}
