<?php

namespace App\Contracts;

use App\Services\Health\HealthCheckResult;

interface HealthCheckerInterface
{
    public function name(): string;

    public function check(): HealthCheckResult;
}
