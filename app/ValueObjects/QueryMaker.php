<?php

namespace App\ValueObjects;

use Stringable;
use Illuminate\Http\Request;

class QueryMaker implements Stringable
{
    public static function init(): static
    {
        return new static();
    }

    public function __toString(): string
    {
        return (string) 'SELECT now()';
    }
}
