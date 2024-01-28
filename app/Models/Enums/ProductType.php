<?php

namespace App\Models\Enums;

enum ProductType: string
{
    case IS_SINGLE = 'is_single';
    case IS_SERVICE = 'is_service';
    case IS_VARIANT = 'is_variant';
}
