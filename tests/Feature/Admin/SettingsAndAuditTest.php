<?php

namespace Tests\Feature\Admin;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Services\SettingsRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsAndAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_update_general_settings_and_it_is_audited(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'site_name' => 'متجر تجريبي',
            'default_currency' => 'USD',
        ])->assertRedirect();

        $settings = app(SettingsRepository::class);
        $this->assertSame('متجر تجريبي', $settings->get('general', 'site_name'));
        $this->assertSame('USD', $settings->get('general', 'default_currency'));

        $this->assertDatabaseHas('audit_logs', [
            'action' => AuditAction::SettingsUpdated->value,
        ]);
        $this->assertNotNull(AuditLog::first()->created_at);
    }

    public function test_tracking_access_token_is_encrypted_at_rest(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->put(route('admin.tracking.update'), [
            'meta_enabled' => '1',
            'meta_pixel_id' => '123456789',
            'meta_capi_enabled' => '1',
            'meta_access_token' => 'super-secret-token',
        ])->assertRedirect();

        // Raw stored value must not equal the plaintext token.
        $raw = \DB::table('settings')->where('group', 'tracking_meta')->where('key', 'access_token')->value('value');
        $this->assertNotNull($raw);
        $this->assertNotSame('super-secret-token', $raw);

        // But it decrypts back through the repository.
        $this->assertSame('super-secret-token', app(SettingsRepository::class)->get('tracking_meta', 'access_token'));
    }

    public function test_pixel_id_must_be_numeric(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->put(route('admin.tracking.update'), [
            'meta_enabled' => '1',
            'meta_pixel_id' => 'not-numeric',
        ])->assertSessionHasErrors('meta_pixel_id');
    }
}
