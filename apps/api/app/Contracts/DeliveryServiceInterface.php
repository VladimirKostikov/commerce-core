<?php

namespace App\Contracts;

use App\Models\Order;

interface DeliveryServiceInterface
{
    public function deliver(Order $order): Order;

    public function recover(Order $order): Order;
}
