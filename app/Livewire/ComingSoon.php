<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class ComingSoon extends Component
{
    public string $slug;
    public string $label = '';
    public string $description = '';
    public array $features = [];

    public function mount(string $slug): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        foreach (config('super_admin_menu', []) as $group) {
            foreach ($group['items'] as $item) {
                if (($item['slug'] ?? null) === $slug) {
                    $this->slug = $slug;
                    $this->label = $item['label'];
                    $this->description = $item['description'] ?? '';
                    $this->features = $item['features'] ?? [];

                    return;
                }
            }
        }

        abort(404);
    }

    public function render()
    {
        return view('livewire.coming-soon');
    }
}
