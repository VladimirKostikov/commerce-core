<?php

namespace Tests\Support;

final class PostgresExplain
{
    public static function usesIndex(array $node, string $index): bool
    {
        if (($node['Index Name'] ?? null) === $index) {
            return true;
        }

        foreach ($node['Plans'] ?? [] as $child) {
            if (self::usesIndex($child, $index)) {
                return true;
            }
        }

        return false;
    }

    public static function nodeTypes(array $node): array
    {
        $types = [(string) ($node['Node Type'] ?? '')];

        foreach ($node['Plans'] ?? [] as $child) {
            $types = array_merge($types, self::nodeTypes($child));
        }

        return $types;
    }
}
