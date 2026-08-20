<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_a_user_with_roles(): void
    {
        $admin = $this->superAdmin();
        $staffRole = Role::where('name', Role::STAFF)->firstOrFail();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'مستخدم جديد',
            'email' => 'new@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$staffRole->id],
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['email' => 'new@example.com']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'created']);
    }

    public function test_user_cannot_delete_themselves(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin))
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
