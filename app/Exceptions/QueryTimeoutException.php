<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class QueryTimeoutException extends RuntimeException
{
    public static function exceeded(int $seconds): self
    {
        return new self("Query exceeded the configured timeout of {$seconds} seconds.", 408);
    }
}
