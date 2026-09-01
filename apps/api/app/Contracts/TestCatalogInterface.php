<?php

namespace App\Contracts;

interface TestCatalogInterface
{
    public function cases(): array;

    public function contains(string $id): bool;
}
