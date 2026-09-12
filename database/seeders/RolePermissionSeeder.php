<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    protected array $modules = [
        'dashboard' => ['view'],
        'production' => ['view', 'create', 'update', 'delete'],
        'inventory' => ['view', 'create', 'update', 'delete'],
        'quality' => ['view', 'create', 'update', 'delete'],
        'sales' => ['view', 'create', 'update', 'delete'],
        'purchase' => ['view', 'create', 'update', 'delete'],
        'employees' => ['view', 'create', 'update', 'delete'],
        'reports' => ['view'],
        'settings' => ['view', 'create', 'update', 'delete'],
        'users' => ['view', 'create', 'update', 'delete'],
    ];

    public function run(): void
    {
        $all = $this->allPermissions();

        foreach ($this->modules as $module => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate(['name' => "$module.$action", 'guard_name' => 'web']);
            }
        }

        $assignments = [
            'admin' => $all,
            'manager' => $all->reject(fn ($p) => $this->isModule($p, 'users'))
                ->reject(fn ($p) => $this->isAction($p, 'delete'))
                ->merge(['settings.view'])->unique(),
            'operator' => $all->filter(fn ($p) => $this->isView($p) || in_array($p, [
                'production.create', 'production.update',
            ]))->reject(fn ($p) => $this->isModule($p, 'users')),
            'inspector' => $all->filter(fn ($p) => $this->isView($p) || in_array($p, [
                'quality.create', 'quality.update',
            ]))->reject(fn ($p) => $this->isModule($p, 'users')),
            'viewer' => $all->filter(fn ($p) => $this->isView($p))
                ->reject(fn ($p) => $this->isModule($p, 'users')),
        ];

        foreach ($assignments as $roleName => $permissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($permissions->values());
        }

        $admin = User::where('email', 'admin@bricks.com')->first();
        if ($admin) {
            $admin->syncRoles(['admin']);
        }
    }

    protected function allPermissions(): Collection
    {
        $permissions = [];

        foreach ($this->modules as $module => $actions) {
            foreach ($actions as $action) {
                $permissions[] = "$module.$action";
            }
        }

        return collect($permissions);
    }

    protected function isModule(string $permission, string $module): bool
    {
        return str_starts_with($permission, "$module.");
    }

    protected function isAction(string $permission, string $action): bool
    {
        return str_ends_with($permission, ".$action");
    }

    protected function isView(string $permission): bool
    {
        return str_ends_with($permission, '.view');
    }
}
