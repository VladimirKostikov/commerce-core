<?php

namespace App\Services\Health;

final readonly class HealthCheckResult
{
    public function __construct(
        public string $name,
        public bool $ok,
        public ?string $message = null,
    ) {
    }

    public static function ok(string $name): self
    {
        return new self($name, true);
    }

    public static function fail(string $name, string $message): self
    {
        return new self($name, false, $message);
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'ok' => $this->ok,
            'message' => $this->message,
        ];
    }
}
