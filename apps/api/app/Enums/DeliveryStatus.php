<?php

namespace App\Enums;

enum DeliveryStatus: string
{
    case Pending = 'pending';
    case Issued = 'issued';
    case Failed = 'failed';
}
