<?php

namespace App\Services\Health;

final readonly class HealthReport
{
    public function __construct(
        private array $results,
    ) {
    }

    public function isHealthy(): bool
    {
        foreach ($this->results as $result) {
            if (! $result->ok) {
                return false;
            }
        }

        return true;
    }

    public function results(): array
    {
        return $this->results;
    }

    public function toArray(): array
    {
        return [
            'status' => $this->isHealthy() ? 'ok' : 'error',
            'checks' => array_map(
                static fn (HealthCheckResult $result): array => $result->toArray(),
                $this->results,
            ),
        ];
    }
}
