<?php

namespace App\Models\Enums;

enum PurchaseStatus: string
{
    case Ordered = 'ordered';
    case Pending = 'pending';
    case Received = 'received';
}
