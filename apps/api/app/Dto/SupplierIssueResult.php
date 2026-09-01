<?php

namespace App\Dto;

use App\Enums\SupplierName;

final readonly class SupplierIssueResult
{
    public const STATUS_OK = 'ok';

    public const STATUS_OUT_OF_STOCK = 'out_of_stock';

    public const STATUS_ERROR = 'error';

    public const STATUS_TIMEOUT = 'timeout';

    private function __construct(
        public string $status,
        public ?string $code,
        public ?string $reason,
        public ?SupplierName $supplier = null,
        public ?string $requestId = null,
    ) {
    }

    public static function ok(string $code): self
    {
        return new self(self::STATUS_OK, $code, null);
    }

    public static function outOfStock(): self
    {
        return new self(self::STATUS_OUT_OF_STOCK, null, 'out_of_stock');
    }

    public static function error(string $reason): self
    {
        return new self(self::STATUS_ERROR, null, $reason);
    }

    public static function timeout(): self
    {
        return new self(self::STATUS_TIMEOUT, null, 'timeout');
    }

    public function attributed(SupplierName $supplier, string $requestId): self
    {
        return new self($this->status, $this->code, $this->reason, $supplier, $requestId);
    }

    public function isOk(): bool
    {
        return $this->status === self::STATUS_OK && $this->code !== null;
    }

    public function isOutOfStock(): bool
    {
        return $this->status === self::STATUS_OUT_OF_STOCK;
    }

    public function isTimeout(): bool
    {
        return $this->status === self::STATUS_TIMEOUT;
    }
}
