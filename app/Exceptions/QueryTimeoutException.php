<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class QueryTimeoutException extends RuntimeException
{
    public static function exceeded(int $seconds): static
    {
        return new static("Query exceeded the configured timeout of {$seconds} seconds.", 408);
    }
}
