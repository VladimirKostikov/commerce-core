<?php

namespace Tests\Unit\Services\Testing;

use App\Services\Testing\PhpCliBinary;
use PHPUnit\Framework\TestCase;

final class PhpCliBinaryTest extends TestCase
{
    public function test_path_is_cli_not_fpm(): void
    {
        $path = PhpCliBinary::path();

        $this->assertNotSame('', $path);
        $this->assertStringNotContainsString('fpm', strtolower($path));
    }
}
