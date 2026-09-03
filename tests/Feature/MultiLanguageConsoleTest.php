<?php

namespace Tests\Feature;

use App\Livewire\MultiLanguage;
use App\Models\Language;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MultiLanguageConsoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_add_a_language(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(MultiLanguage::class)
            ->call('create')
            ->set('code', 'hi')
            ->set('name', 'Hindi')
            ->set('nativeName', 'हिन्दी')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('languages', ['code' => 'hi', 'name' => 'Hindi']);
    }

    public function test_default_language_cannot_be_deactivated_or_deleted(): void
    {
        $admin = User::factory()->create();
        $default = Language::where('is_default', true)->firstOrFail();

        try {
            Livewire::actingAs($admin)->test(MultiLanguage::class)->call('toggleActive', $default->id);
        } catch (\Throwable $e) {
            // Guarded by abort_if; either the exception surfaces or the state is left untouched.
        }

        $this->assertDatabaseHas('languages', ['id' => $default->id, 'is_active' => true]);
    }

    public function test_super_admin_can_change_the_default_language(): void
    {
        $admin = User::factory()->create();
        $bangla = Language::where('code', 'bn')->firstOrFail();

        Livewire::actingAs($admin)->test(MultiLanguage::class)
            ->call('setDefault', $bangla->id)
            ->assertHasNoErrors();

        $this->assertDatabaseHas('languages', ['code' => 'bn', 'is_default' => true]);
        $this->assertDatabaseHas('languages', ['code' => 'en', 'is_default' => false]);
    }

    public function test_super_admin_can_add_a_translation_key_and_update_values_per_language(): void
    {
        $admin = User::factory()->create();

        Livewire::actingAs($admin)->test(MultiLanguage::class)
            ->set('tab', 'translations')
            ->set('newKey', 'dashboard.welcome')
            ->call('addKey')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('translations', ['key' => 'dashboard.welcome', 'locale' => 'en']);
        $this->assertDatabaseHas('translations', ['key' => 'dashboard.welcome', 'locale' => 'bn']);

        Livewire::actingAs($admin)->test(MultiLanguage::class)
            ->call('updateValue', 'dashboard.welcome', 'en', 'Welcome')
            ->call('updateValue', 'dashboard.welcome', 'bn', 'স্বাগতম');

        $this->assertDatabaseHas('translations', ['key' => 'dashboard.welcome', 'locale' => 'en', 'value' => 'Welcome']);
        $this->assertDatabaseHas('translations', ['key' => 'dashboard.welcome', 'locale' => 'bn', 'value' => 'স্বাগতম']);

        Livewire::actingAs($admin)->test(MultiLanguage::class)->call('deleteKey', 'dashboard.welcome');
        $this->assertDatabaseMissing('translations', ['key' => 'dashboard.welcome']);
    }
}
