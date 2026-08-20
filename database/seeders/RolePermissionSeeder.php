<?php

namespace Database\Seeders;

use App\Enums\Permission as PermissionEnum;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Permissions from the enum catalogue.
        foreach (PermissionEnum::cases() as $case) {
            Permission::updateOrCreate(
                ['name' => $case->value],
                ['display_name' => $case->label()],
            );
        }

        // Super Admin — all permissions.
        $superAdmin = Role::updateOrCreate(
            ['name' => Role::SUPER_ADMIN],
            ['display_name' => 'مدير عام'],
        );
        $superAdmin->permissions()->sync(Permission::pluck('id'));

        // Staff — operational permissions only.
        $staff = Role::updateOrCreate(
            ['name' => Role::STAFF],
            ['display_name' => 'موظف'],
        );
        $staffPermissionIds = Permission::whereIn(
            'name',
            array_map(fn (PermissionEnum $p) => $p->value, PermissionEnum::staffDefaults()),
        )->pluck('id');
        $staff->permissions()->sync($staffPermissionIds);
    }
}
