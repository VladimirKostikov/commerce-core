<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class OrderNotFoundException extends NotFoundHttpException
{
    public function __construct()
    {
        parent::__construct('Order not found');
    }
}
