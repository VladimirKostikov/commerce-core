<?php

namespace App\Enums;

enum SupplierMode: string
{
    case Ok = 'ok';
    case Fail = 'fail';
    case Hang = 'hang';
}
