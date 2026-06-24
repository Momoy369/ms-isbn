<?php

namespace App\Http\Controllers;

use App\Services\ProductionReportService;

class ProductionReportController
{
    public function index(
        ProductionReportService $service
    ) {
        $report =

            $service->monthly(

                now()->year,

                now()->month

            );

        return view(

            'reports.production',

            compact('report')

        );
    }
}