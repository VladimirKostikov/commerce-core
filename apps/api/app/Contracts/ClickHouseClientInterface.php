<?php

namespace App\Contracts;

interface ClickHouseClientInterface
{
    public function insertJson(string $table, array $row): void;

    public function insertJsonRows(string $table, array $rows): void;

    public function query(string $sql): string;

    public function execute(string $sql): void;
}
