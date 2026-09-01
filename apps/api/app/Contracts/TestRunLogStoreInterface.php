<?php

namespace App\Contracts;

interface TestRunLogStoreInterface
{
    public function put(string $id, string $output): void;

    public function get(string $id): ?string;
}
