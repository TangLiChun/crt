<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Domain\Services\KpiService;

final class KpiController
{
    public function __construct(private readonly KpiService $kpis)
    {
    }

    public function index(Request $request, array $user): Response
    {
        return Response::view('kpi/index', array_merge(
            $this->kpis->dashboard($user),
            ['pageTitle' => 'KPI 看板']
        ));
    }
}
