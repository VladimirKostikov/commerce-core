<?php

namespace Tests\Fakes;

use App\Contracts\HealthCheckerInterface;
use App\Services\Health\HealthCheckResult;

final class FakeHealthChecker implements HealthCheckerInterface
{
    public function __construct(
        private readonly string $checkerName,
        private readonly bool $ok,
        private readonly ?string $message = null,
    ) {
    }

    public function name(): string
    {
        return $this->checkerName;
    }

    public function check(): HealthCheckResult
    {
        if ($this->ok) {
            return HealthCheckResult::ok($this->checkerName);
        }

        return HealthCheckResult::fail($this->checkerName, (string) $this->message);
    }
}
