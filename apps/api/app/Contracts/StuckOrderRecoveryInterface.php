<?php

namespace App\Contracts;

interface StuckOrderRecoveryInterface
{
    public function recoverAll(): int;
}
