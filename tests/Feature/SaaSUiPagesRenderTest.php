<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaaSUiPagesRenderTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_console_ui_pages_render(): void
    {
        $admin = User::factory()->create();

        $pages = [
            ['/feature-modules', 'Feature & modules'],
            ['/white-label', 'White label'],
            ['/customer-app', 'Customer app'],
            ['/media-server', 'Media / movie server'],
            ['/network-integrations', 'Network integrations'],
            ['/api-management', 'API management'],
            ['/system-settings', 'System settings'],
            ['/system-health', 'System health'],
            ['/queue-jobs', 'Queue & jobs'],
            ['/data-management', 'Data management'],
            ['/support-tickets', 'Support tickets'],
            ['/reports-analytics', 'Reports & analytics'],
            ['/platform-analytics', 'Platform analytics'],
            ['/platform-users', 'Platform users'],
            ['/roles-permissions', 'Roles & permissions'],
            ['/security-center', 'Security center'],
            ['/audit-activity', 'Audit activity'],
            ['/notifications', 'Notifications'],
            ['/sms-management', 'SMS management'],
            ['/my-profile', 'My profile'],
        ];

        foreach ($pages as [$url, $heading]) {
            $this->actingAs($admin)->get($url)->assertOk()->assertSee($heading);
        }
    }
}
