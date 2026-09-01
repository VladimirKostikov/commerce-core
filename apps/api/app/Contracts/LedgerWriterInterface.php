<?php

namespace App\Contracts;

use App\Models\Order;

interface LedgerWriterInterface
{
    public function postPayment(Order $order): void;

    public function postIssue(Order $order): void;
}
