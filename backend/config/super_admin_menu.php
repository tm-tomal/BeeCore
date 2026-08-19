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
                'slug' => 'sms-management',
                'description' => 'SMS providers, credit, and delivery operations.',
                'features' => ['Add/configure SMS provider', 'Sender ID & pricing', 'Tenant SMS balance/credit', 'Delivery reports & failed SMS', 'SMS queue & logs', 'Templates & usage report'],
            ],
            [
                'label' => 'Email management',
                'slug' => 'email-management',
                'description' => 'Email providers, templates, and delivery operations.',
                'features' => ['SMTP / email API provider', 'Templates (transactional & bulk)', 'Email quota & usage', 'Delivery status & failed email', 'Email logs & reports'],
            ],
            [
                'label' => 'Notifications',
                'slug' => 'notifications',
                'description' => 'Global notification templates and delivery settings.',
                'features' => ['Global notification settings', 'SMS/email/push templates', 'Billing & lifecycle notifications', 'System maintenance notices', 'Notification logs'],
            ],
            [
                'label' => 'Announcements',
                'slug' => 'announcements',
                'description' => 'Broadcast messages to tenants and customers.',
                'features' => ['Create/edit/delete/publish', 'Schedule announcement', 'Global or tenant-specific', 'Maintenance/feature/payment notices', 'Email/SMS/push/dashboard delivery'],
            ],
        ],
    ],
    [
        'group' => 'Network & integrations',
        'items' => [
            [
                'label' => 'Network integrations',
                'slug' => 'network-integrations',
                'description' => 'MikroTik, RADIUS, OLT and custom API integrations.',
                'features' => ['MikroTik / RADIUS / OLT integration', 'Custom API integration', 'Enable/disable per tenant', 'Integration health & version', 'API request/response/failure logs'],
            ],
            [
                'label' => 'API management',
                'slug' => 'api-management',
                'description' => 'Manage API clients, tokens, and usage.',
                'features' => ['API clients, keys & tokens', 'Rate limits & usage', 'API logs & failed requests', 'Webhooks & webhook logs', 'API versions & documentation'],
            ],
        ],
    ],
    [
        'group' => 'Operations',
        'items' => [
            [
                'label' => 'System settings',
                'slug' => 'system-settings',
                'description' => 'Editable, audited platform configuration.',
                'features' => ['Platform name/logo/favicon', 'Default language, currency & timezone', 'Invoice/email/SMS/payment settings', 'Security & session settings', 'Rate limit & storage settings'],
            ],
            [
                'label' => 'System health',
                'slug' => 'system-health',
                'description' => 'Live infrastructure and service health monitoring.',
                'features' => ['Server CPU/RAM/disk/network usage', 'Database, Redis & queue status', 'Scheduler & API status', 'Background jobs & failed jobs', 'System alerts'],
            ],
            [
                'label' => 'Queue & jobs',
                'slug' => 'queue-jobs',
                'description' => 'Background job monitoring and control.',
                'features' => ['Pending/running/completed/failed jobs', 'Retry / cancel / delete job', 'Job logs & queue monitoring', 'SMS/email/payment/notification job queues'],
            ],
            [
                'label' => 'Data management',
                'slug' => 'data-management',
                'description' => 'Backups, retention, and tenant data operations.',
                'features' => ['Database backup status & history', 'Backup configuration', 'Tenant data export & archive', 'Data retention', 'Recovery workflow', 'Data import & migration tools'],
            ],
        ],
    ],
    [
        'group' => 'Support',
        'items' => [
            [
                'label' => 'Support tickets',
                'slug' => 'support-tickets',
                'description' => 'Cross-tenant support ticket management.',
                'features' => ['Ticket lifecycle (open → resolved/closed)', 'Priority & category', 'Assign support agent', 'SLA management', 'Response/resolution time', 'Support performance & reports'],
            ],
        ],
    ],
    [
        'group' => 'Reports & analytics',
        'items' => [
            [
                'label' => 'Reports & analytics',
                'slug' => 'reports-analytics',
                'description' => 'Dedicated SaaS reporting beyond the dashboard summary.',
                'features' => ['ISP & customer growth report', 'Revenue report (subscription, add-on, SMS)', 'Trial conversion & churn rate', 'Plan/add-on distribution', 'Payment success/failure rate', 'Export reports'],
            ],
            [
                'label' => 'Platform analytics',
                'slug' => 'platform-analytics',
                'description' => 'Deeper platform-wide metrics beyond the dashboard summary.',
                'features' => ['ARPU', 'Metered SMS/API/storage usage', 'Tenant growth trend', 'Analytics history'],
            ],
        ],
    ],
    [
        'group' => 'Security & access',
        'items' => [
            ['label' => 'Platform users', 'route' => 'platform-users'],
            [
                'label' => 'Roles & permissions',
                'slug' => 'roles-permissions',
                'description' => 'Configurable roles and granular permissions.',
                'features' => ['Create/edit/delete role', 'Assign/remove permission', 'Platform-level vs tenant-level permission', 'Financial/network/security/audit permission', 'Permission change audit'],
            ],
            [
                'label' => 'Security center',
                'slug' => 'security-center',
                'description' => 'Advanced authentication and account security controls.',
                'features' => ['Failed login & lockout', 'Suspicious activity & IP monitoring', 'IP blocking', '2FA & API tokens', 'Session/device management', 'Password policy & rate limiting'],
            ],
            ['label' => 'Audit activity', 'route' => 'audit-activity'],
        ],
    ],
    [
        'group' => 'Account',
        'items' => [
            [
                'label' => 'My profile',
                'slug' => 'my-profile',
                'description' => 'Dedicated Super Admin profile and security preferences.',
                'features' => ['Profile information', 'Change password', '2FA', 'Active sessions & login history', 'Notification preferences', 'Language & timezone'],
            ],
        ],
    ],
];
