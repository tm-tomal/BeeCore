<?php

// Full Super Admin navigation, matching backend/super-admin.md.
// The "Dashboard" link above this menu already covers section 1 (Dashboard),
// so it is intentionally not repeated here.
// Each item is either "route" => an existing named route, or "slug" => a
// coming-soon placeholder page rendered with the listed planned features.
return [
    [
        'group' => 'ISP / Tenant',
        'items' => [
            ['label' => 'Tenants', 'route' => 'tenants'],
            ['label' => 'ISP onboarding', 'route' => 'isp-onboarding'],
        ],
    ],
    [
        'group' => 'Subscriptions & billing',
        'items' => [
            ['label' => 'SaaS plans', 'route' => 'saas-plans'],
            ['label' => 'Subscriptions', 'route' => 'subscriptions'],
            ['label' => 'SaaS billing', 'route' => 'saas-billing'],
            ['label' => 'SaaS payments', 'route' => 'saas-payments'],
            ['label' => 'Payment gateways', 'route' => 'payment-gateways'],
            ['label' => 'Add-ons', 'route' => 'add-ons'],
        ],
    ],
    [
        'group' => 'Platform features',
        'items' => [
            [
                'label' => 'Feature & modules',
                'route' => 'feature-modules',
            ],
            [
                'label' => 'Multi-language',
                'route' => 'multi-language',
            ],
            [
                'label' => 'Multi-currency',
                'route' => 'multi-currency',
            ],
            [
                'label' => 'White label',
                'route' => 'white-label',
            ],
            [
                'label' => 'Customer app',
                'route' => 'customer-app',
            ],
            [
                'label' => 'Media server',
                'route' => 'media-server',
            ],
        ],
    ],
    [
        'group' => 'Communications',
        'items' => [
            [
                'label' => 'SMS management',
                'route' => 'sms-management',
            ],
            [
                'label' => 'Email management',
                'route' => 'email-management',
            ],
            [
                'label' => 'Notifications',
                'route' => 'notifications',
            ],
            [
                'label' => 'Announcements',
                'route' => 'announcements',
            ],
        ],
    ],
    [
        'group' => 'Network & integrations',
        'items' => [
            [
                'label' => 'Network integrations',
                'route' => 'network-integrations',
            ],
            [
                'label' => 'API management',
                'route' => 'api-management',
            ],
        ],
    ],
    [
        'group' => 'Operations',
        'items' => [
            [
                'label' => 'System settings',
                'route' => 'system-settings',
            ],
            [
                'label' => 'System health',
                'route' => 'system-health',
            ],
            [
                'label' => 'Queue & jobs',
                'route' => 'queue-jobs',
            ],
            [
                'label' => 'Data management',
                'route' => 'data-management',
            ],
        ],
    ],
    [
        'group' => 'Support',
        'items' => [
            [
                'label' => 'Support tickets',
                'route' => 'support-tickets',
            ],
        ],
    ],
    [
        'group' => 'Reports & analytics',
        'items' => [
            [
                'label' => 'Reports & analytics',
                'route' => 'reports-analytics',
            ],
            [
                'label' => 'Platform analytics',
                'route' => 'platform-analytics',
            ],
        ],
    ],
    [
        'group' => 'Security & access',
        'items' => [
            ['label' => 'Platform users', 'route' => 'platform-users'],
            [
                'label' => 'Roles & permissions',
                'route' => 'roles-permissions',
            ],
            [
                'label' => 'Security center',
                'route' => 'security-center',
            ],
            ['label' => 'Audit activity', 'route' => 'audit-activity'],
        ],
    ],
    [
        'group' => 'Account',
        'items' => [
            [
                'label' => 'My profile',
                'route' => 'my-profile',
            ],
        ],
    ],
];
