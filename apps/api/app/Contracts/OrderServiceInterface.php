<?php

namespace App\Contracts;

use App\Models\Order;
use App\Models\User;

interface OrderServiceInterface
{
    public function create(User $user, string $sku): Order;

    public function findForUser(User $user, string $id): Order;
}
