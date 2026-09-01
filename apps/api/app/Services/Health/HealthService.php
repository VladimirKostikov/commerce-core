<?php

namespace App\Services\Health;

use App\Contracts\HealthServiceInterface;

final class HealthService implements HealthServiceInterface
{
    public function __construct(
        private readonly iterable $checkers,
    ) {
    }

    public function report(): HealthReport
    {
        $results = [];

        foreach ($this->checkers as $checker) {
            $results[] = $checker->check();
        }

        return new HealthReport($results);
    }
}
