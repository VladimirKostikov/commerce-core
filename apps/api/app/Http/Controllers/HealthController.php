<?php

namespace App\Http\Controllers;

use App\Contracts\HealthServiceInterface;
use Illuminate\Http\JsonResponse;

final class HealthController extends Controller
{
    public function __invoke(HealthServiceInterface $healthService): JsonResponse
    {
        $report = $healthService->report();
        $status = $report->isHealthy() ? 200 : 503;

        return response()->json($report->toArray(), $status);
    }
}
