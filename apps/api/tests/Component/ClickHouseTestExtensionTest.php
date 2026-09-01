<?php

namespace Tests\Component;

use PHPUnit\Runner\Extension\Extension;
use Tests\Support\PhpUnit\ClickHouseTestExtension;
use Tests\TestCase;

final class ClickHouseTestExtensionTest extends TestCase
{
    public function test_extension_is_a_phpunit_extension(): void
    {
        $this->assertInstanceOf(Extension::class, new ClickHouseTestExtension());
    }
}
