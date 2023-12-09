<?php

namespace App\Models\Casts;

enum ShipmentStatus: string
{
    case ORDERED = 'ordered';
    case PACKED = 'packed';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
}
