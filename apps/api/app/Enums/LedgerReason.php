<?php

namespace App\Enums;

enum LedgerReason: string
{
    case PaymentReceived = 'payment_received';
    case DeliveryLiability = 'delivery_liability';
    case LiabilityCleared = 'liability_cleared';
    case KeysIssued = 'keys_issued';
}
