<?php

namespace Tests\E2E;

use PHPUnit\Framework\TestCase;
use Tests\Support\HttpProbe;
use Tests\Support\InfrastructureHost;

final class CatalogStorefrontLiveTest extends TestCase
{
    public function test_catalog_list_via_nginx(): void
    {
        $response = HttpProbe::get(InfrastructureHost::appUrl().'/api/catalog?limit=20');
        $payload = json_decode($response['body'], true);

        $this->assertSame(200, $response['status']);
        $this->assertIsArray($payload);
        $this->assertArrayHasKey('items', $payload);
        $this->assertNotEmpty($payload['items']);
        $this->assertArrayHasKey('sku', $payload['items'][0]);
        $this->assertGreaterThan(0, $payload['items'][0]['available_keys_count']);
    }
}
