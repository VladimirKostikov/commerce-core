<?php

namespace App\Http\Controllers;

use App\Contracts\ReconciliationServiceInterface;
use Illuminate\Http\JsonResponse;

final class ReconciliationController extends Controller
{
    public function __invoke(ReconciliationServiceInterface $reconciliation): JsonResponse
    {
        return response()->json($reconciliation->report()->toArray());
    }
}
