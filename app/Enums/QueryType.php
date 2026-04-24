<?php

declare(strict_types=1);

namespace App\Enums;

enum QueryType: string
{
    case Raw    = 'raw';
    case Select = 'select';
    case Insert = 'insert';
    case Update = 'update';
    case Delete = 'delete';
}
