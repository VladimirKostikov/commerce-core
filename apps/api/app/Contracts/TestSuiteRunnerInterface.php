<?php

namespace App\Contracts;

use App\Dto\TestRunResult;

interface TestSuiteRunnerInterface
{
    public function run(string $suite): TestRunResult;

    public function runCase(string $id): TestRunResult;
}
