<?php

namespace App\Services\Testing;

final class KnownTestSuites
{
    public const ALL = 'all';

    public static function names(): array
    {
        return [
            self::ALL,
            'Unit',
            'Component',
            'Feature',
            'Api',
            'Functional',
            'Database',
            'Integration',
            'System',
            'E2E',
            'Performance',
        ];
    }

    public static function contains(string $suite): bool
    {
        return in_array($suite, self::names(), true);
    }

    public static function directories(): array
    {
        return [
            'Unit' => 'Unit',
            'Component' => 'Component',
            'Feature' => 'Feature',
            'Api' => 'Api',
            'Functional' => 'Functional',
            'Database' => 'Database',
            'Integration' => 'Integration',
            'System' => 'System',
            'E2E' => 'E2E',
            'Performance' => 'Performance',
        ];
    }
}
