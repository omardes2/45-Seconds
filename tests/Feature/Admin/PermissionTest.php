<?php

namespace Tests\Feature\Admin;

use App\Enums\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_users_and_settings(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.settings.edit'))->assertOk();
        $this->actingAs($admin)->get(route('admin.tracking.edit'))->assertOk();
    }

    public function test_staff_cannot_access_users_or_settings(): void
    {
        $staff = $this->staff();

        $this->actingAs($staff)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.settings.edit'))->assertForbidden();
    }

    public function test_staff_can_access_tracking(): void
    {
        $staff = $this->staff();

        $this->actingAs($staff)->get(route('admin.tracking.edit'))->assertOk();
    }

    public function test_audit_log_is_restricted_to_settings_managers(): void
    {
        $this->actingAs($this->staff())->get(route('admin.audit-logs.index'))->assertForbidden();
        $this->actingAs($this->superAdmin())->get(route('admin.audit-logs.index'))->assertOk();
    }

    public function test_super_admin_bypasses_all_gates(): void
    {
        $admin = $this->superAdmin();

        $this->assertTrue($admin->can(Permission::ManageSettings->value));
        $this->assertTrue($admin->can(Permission::ManageUsers->value));
    }
}
