<?php

declare(strict_types=1);

$demoUsers = [
    [
        'username' => 'admin',
        'password' => 'admin123456',
        'display_name' => '销售管理员',
        'role' => 'manager',
    ],
    [
        'username' => 'sales',
        'password' => 'sales123456',
        'display_name' => '陈露',
        'role' => 'sales',
    ],
    [
        'username' => 'sales2',
        'password' => 'sales223456',
        'display_name' => '王宁',
        'role' => 'sales',
    ],
];

return [
    'name' => 'Sales Rhythm CRM',
    'timezone' => 'Asia/Shanghai',
    'page_size' => 20,
    'db_path' => getenv('CRM_DB_PATH') ?: __DIR__ . '/../database/crm.sqlite',
    'session_name' => 'sales_crm_session',
    'post_rules' => [
        'default_min_gap_hours' => 72,
        'default_stale_gap_hours' => 168,
    ],
    'demo_users' => $demoUsers,
    'demo_user' => $demoUsers[0],
];
