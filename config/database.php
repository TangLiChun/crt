<?php

declare(strict_types=1);

return [
    'path' => getenv('CRM_DB_PATH') ?: __DIR__ . '/../database/crm.sqlite',
    'busy_timeout' => 5000,
    'journal_mode' => 'WAL',
    'foreign_keys' => true,
];
