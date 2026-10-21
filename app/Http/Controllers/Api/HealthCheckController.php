<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HealthCheckService;
use Illuminate\Http\JsonResponse;

class HealthCheckController extends Controller
{
    public function __construct(
        protected HealthCheckService $healthCheckService
    ) {}

    /**
     * Check system health and return report.
     */
    public function check(): JsonResponse
    {
        $report = $this->healthCheckService->check();

        $statusCode = match ($report['status']) {
            'ok', 'degraded' => 200,
            default => 503,
        };

        return response()->json($report, $statusCode);
    }
}
