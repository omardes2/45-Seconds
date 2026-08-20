<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Avoid depending on a built Vite manifest during the test suite.
        $this->withoutVite();
    }

    /**
     * Seed the RBAC catalogue and return a user with the given role.
     */
    protected function userWithRole(string $roleName): User
    {
        $this->seed(RolePermissionSeeder::class);

        $user = User::factory()->create();
        $role = Role::where('name', $roleName)->firstOrFail();
        $user->roles()->attach($role);

        return $user->fresh();
    }

    protected function superAdmin(): User
    {
        return $this->userWithRole(Role::SUPER_ADMIN);
    }

    protected function staff(): User
    {
        return $this->userWithRole(Role::STAFF);
    }
}
