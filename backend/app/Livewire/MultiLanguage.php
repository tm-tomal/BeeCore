<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\Language;
use App\Models\Translation;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class MultiLanguage extends Component
{
    public string $tab = 'languages';

    // Language form
    public string $viewMode = 'index';
    public ?int $languageId = null;
    public string $code = '';
    public string $name = '';
    public string $nativeName = '';

    // Translation form
    public string $newKey = '';
    public array $newValues = [];

    protected function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:10', Rule::unique('languages', 'code')->ignore($this->languageId)],
            'name' => ['required', 'string', 'max:255'],
            'nativeName' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function create(): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $this->reset(['languageId', 'code', 'name', 'nativeName']);
        $this->viewMode = 'create';
    }

    public function edit(int $id): void
    {
        $this->assertSuperAdmin();
        $this->resetValidation();
        $language = Language::findOrFail($id);
        $this->languageId = $language->id;
        $this->code = $language->code;
        $this->name = $language->name;
        $this->nativeName = $language->native_name ?? '';
        $this->viewMode = 'create';
    }

    public function cancelForm(): void
    {
        $this->viewMode = 'index';
    }

    public function save(): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate();

        $language = $this->languageId ? Language::findOrFail($this->languageId) : new Language(['is_active' => true]);
        $language->fill([
            'code' => $data['code'],
            'name' => $data['name'],
            'native_name' => $data['nativeName'] ?: null,
        ])->save();

        AuditLog::record($this->languageId ? 'language.updated' : 'language.created', $language, ['code' => $language->code]);

        $this->viewMode = 'index';
        session()->flash('message', $this->languageId ? 'Language updated.' : 'Language added.');
    }

    public function toggleActive(int $id): void
    {
        $this->assertSuperAdmin();
        $language = Language::findOrFail($id);
        abort_if($language->is_default && $language->is_active, 422, 'The default language cannot be deactivated.');
        $language->update(['is_active' => !$language->is_active]);
        AuditLog::record($language->is_active ? 'language.activated' : 'language.deactivated', $language);
        session()->flash('message', 'Language '.($language->is_active ? 'activated' : 'deactivated').'.');
    }

    public function setDefault(int $id): void
    {
        $this->assertSuperAdmin();
        $language = Language::findOrFail($id);
        abort_unless($language->is_active, 422, 'Only an active language can become the default.');

        Language::where('is_default', true)->update(['is_default' => false]);
        $language->update(['is_default' => true]);
        AuditLog::record('language.default_set', $language);
        session()->flash('message', $language->name.' is now the default language.');
    }

    public function delete(int $id): void
    {
        $this->assertSuperAdmin();
        $language = Language::findOrFail($id);
        abort_if($language->is_default, 422, 'The default language cannot be deleted.');

        Translation::where('locale', $language->code)->delete();
        $language->delete();
        AuditLog::record('language.deleted', null, ['code' => $language->code]);
        session()->flash('message', 'Language removed.');
    }

    public function addKey(): void
    {
        $this->assertSuperAdmin();
        $data = $this->validate(['newKey' => ['required', 'string', 'max:255']]);

        $activeLocales = Language::where('is_active', true)->pluck('code');
        foreach ($activeLocales as $locale) {
            Translation::firstOrCreate(['key' => $data['newKey'], 'locale' => $locale], ['value' => '']);
        }

        AuditLog::record('translation.key_added', null, ['key' => $data['newKey']]);
        $this->newKey = '';
        session()->flash('message', 'Translation key added.');
    }

    public function updateValue(string $key, string $locale, string $value): void
    {
        $this->assertSuperAdmin();
        Translation::updateOrCreate(['key' => $key, 'locale' => $locale], ['value' => $value]);
    }

    public function deleteKey(string $key): void
    {
        $this->assertSuperAdmin();
        Translation::where('key', $key)->delete();
        AuditLog::record('translation.key_deleted', null, ['key' => $key]);
        session()->flash('message', 'Translation key deleted.');
    }

    public function render()
    {
        $this->assertSuperAdmin();

        $activeLanguages = Language::where('is_active', true)->orderBy('name')->get();
        $keys = Translation::query()->select('key')->distinct()->orderBy('key')->pluck('key');
        $translationGrid = Translation::query()->get()->groupBy('key');

        return view('livewire.multi-language', [
            'languages' => Language::query()->orderBy('name')->get(),
            'activeLanguages' => $activeLanguages,
            'keys' => $keys,
            'translationGrid' => $translationGrid,
        ]);
    }

    private function assertSuperAdmin(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }
}
