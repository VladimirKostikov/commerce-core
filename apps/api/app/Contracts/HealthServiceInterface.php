<?php

namespace App\Contracts;

use App\Services\Health\HealthReport;

interface HealthServiceInterface
{
    public function report(): HealthReport;
}
