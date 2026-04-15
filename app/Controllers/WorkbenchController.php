<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Domain\Services\WorkbenchService;

final class WorkbenchController
{
    public function __construct(private readonly WorkbenchService $workbench)
    {
    }

    public function index(Request $request, array $user): Response
    {
        return Response::view('workbench/index', array_merge(
            $this->workbench->snapshot($user),
            ['pageTitle' => '销售工作台']
        ));
    }
}
