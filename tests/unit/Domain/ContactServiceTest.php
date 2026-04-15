<?php

declare(strict_types=1);

use App\Domain\Repositories\ContactRepository;
use App\Domain\Services\AccessService;
use App\Domain\Services\ContactService;

test('ContactService 可以创建潜在客户', function (): void {
    [$dbPath, $pdo] = boot_test_database();
    $service = new ContactService(new ContactRepository($pdo, 20), new AccessService());

    $id = $service->save([
        'name' => '测试联系人',
        'contact_type' => 'lead',
        'stage' => '新线索',
        'company_name' => '测试公司',
    ], ['id' => 2, 'role' => 'sales']);

    $row = $pdo->query('SELECT name, contact_type FROM contacts WHERE id = ' . (int) $id)->fetch();
    assert_same('测试联系人', $row['name']);
    assert_same('lead', $row['contact_type']);

    @unlink($dbPath);
});
