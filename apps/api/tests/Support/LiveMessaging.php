<?php

namespace Tests\Support;

final class LiveMessaging
{
    public static function topic(): string
    {
        return 'commerce.test.'.str_replace('.', '', uniqid('', true));
    }

    public static function queue(): string
    {
        return 'commerce.test.'.str_replace('.', '', uniqid('', true));
    }

    public static function group(): string
    {
        return 'commerce-test-'.str_replace('.', '', uniqid('', true));
    }
}
