<?php

// Full Super Admin navigation, matching backend/super-admin.md.
// The "Dashboard" link above this menu already covers section 1 (Dashboard),
// so it is intentionally not repeated here.
// Each item is either "route" => an existing named route, or "slug" => a
// coming-soon placeholder page rendered with the listed planned features.
return [
    [
        'group' => 'Portfolio',
        'items' => [
            ['label' => 'ISP tenants', 'route' => 'tenants'],
        ],
    ],
    [
        'group' => 'Commercial',
        'items' => [
            ['label' => 'Plan & pricing', 'route' => 'saas-plans'],
            ['label' => 'Subscriptions', 'route' => 'subscriptions'],
            ['label' => 'SaaS billing', 'route' => 'saas-billing'],
            ['label' => 'Payment methods', 'route' => 'payment-gateways'],
            ['label' => 'Add-ons', 'route' => 'add-ons'],
        ],
    ],
    [
        'group' => 'Platform',
        'items' => [
            [
                'label' => 'Feature flags',
                'route' => 'feature-modules',
            ],
            [
                'label' => 'Languages',
                'route' => 'multi-language',
            ],
            [
                'label' => 'Currencies',
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
        'group' => 'Messaging',
        'items' => [
            [
                'label' => 'SMS',
                'route' => 'sms-management',
            ],
            [
                'label' => 'Email',
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
        'group' => 'Integrations',
        'items' => [
            [
                'label' => 'Network',
                'route' => 'network-integrations',
            ],
            [
                'label' => 'API & webhooks',
                'route' => 'api-management',
            ],
        ],
    ],
    [
        'group' => 'System',
        'items' => [
            [
                'label' => 'Settings',
                'route' => 'system-settings',
            ],
            [
                'label' => 'Health',
                'route' => 'system-health',
            ],
            [
                'label' => 'Queue',
                'route' => 'queue-jobs',
            ],
            [
                'label' => 'Data & backups',
                'route' => 'data-management',
            ],
        ],
    ],
    [
        'group' => 'Support',
        'items' => [
            [
                'label' => 'Tickets',
                'route' => 'support-tickets',
            ],
        ],
    ],
    [
        'group' => 'Insights',
        'items' => [
            [
                'label' => 'Reports',
                'route' => 'reports-analytics',
            ],
            [
                'label' => 'Platform analytics',
                'route' => 'platform-analytics',
            ],
        ],
    ],
    [
        'group' => 'Security',
        'items' => [
            ['label' => 'Users', 'route' => 'platform-users'],
            [
                'label' => 'Roles & access',
                'route' => 'roles-permissions',
            ],
            [
                'label' => 'Security center',
                'route' => 'security-center',
            ],
            ['label' => 'Audit log', 'route' => 'audit-activity'],
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
