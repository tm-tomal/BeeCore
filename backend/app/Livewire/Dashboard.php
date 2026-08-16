<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public array $metrics = [
        'tenants' => 18,
        'customers' => 12430,
        'monthly_revenue' => 18645,
        'sms_usage' => 48200,
        'active_services' => 96,
        'pending_billing' => 142,
    ];

    public function render()
    {
        return view('livewire.dashboard');
    }
}
