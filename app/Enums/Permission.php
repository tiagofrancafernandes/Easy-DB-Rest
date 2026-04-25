<?php

declare(strict_types=1);

namespace App\Enums;

enum Permission: string
{
    case VIEW = 'view';
    case EDIT = 'edit';
    case FULL = 'full';
}
