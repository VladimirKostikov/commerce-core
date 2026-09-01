<?php

namespace App\Dto;

final readonly class TestRunResult
{
    public function __construct(
        public bool $ok,
        public string $suite,
        public string $output,
    ) {
    }
}
