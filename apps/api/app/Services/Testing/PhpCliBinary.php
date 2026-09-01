<?php

namespace App\Services\Testing;

final class PhpCliBinary
{
    public static function path(): string
    {
        if (! self::isFpm(PHP_BINARY) && is_executable(PHP_BINARY)) {
            return PHP_BINARY;
        }

        $cli = PHP_BINDIR.DIRECTORY_SEPARATOR.'php';

        if (is_executable($cli)) {
            return $cli;
        }

        return 'php';
    }

    private static function isFpm(string $binary): bool
    {
        return str_contains(strtolower(basename($binary)), 'fpm');
    }
}
