<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ProductNotFoundException extends NotFoundHttpException
{
    public function __construct()
    {
        parent::__construct('Product not found');
    }
}
