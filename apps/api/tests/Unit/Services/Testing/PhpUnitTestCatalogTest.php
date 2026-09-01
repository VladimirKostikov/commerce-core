<?php

namespace Tests\Unit\Services\Testing;

use App\Services\Testing\PhpUnitTestCatalog;
use PHPUnit\Framework\TestCase;

final class PhpUnitTestCatalogTest extends TestCase
{
    public function test_lists_home_page_case(): void
    {
        $catalog = new PhpUnitTestCatalog(dirname(__DIR__, 3));
        $id = 'Tests\\Feature\\HomePageTest::test_home_page_renders';

        $this->assertTrue($catalog->contains($id));
    }
}
