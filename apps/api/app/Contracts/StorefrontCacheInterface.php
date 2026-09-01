<?php

namespace App\Contracts;

use Closure;

interface StorefrontCacheInterface
{
    public function remember(int $limit, Closure $resolve): array;

    public function bust(): void;
}
