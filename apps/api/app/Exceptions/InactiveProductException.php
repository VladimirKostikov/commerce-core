<?php

namespace App\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

final class InactiveProductException extends HttpException
{
    public function __construct()
    {
        parent::__construct(422, 'Product is inactive');
    }
}
