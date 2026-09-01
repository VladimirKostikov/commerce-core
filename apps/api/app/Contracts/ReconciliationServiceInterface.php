<?php

namespace App\Contracts;

use App\Dto\ReconciliationReport;

interface ReconciliationServiceInterface
{
    public function report(): ReconciliationReport;
}
