<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class ConnectionException extends RuntimeException
{
    public static function driverNotAllowed(string $driver): self
    {
        return new self("Driver '{$driver}' is not in the allowed drivers list.", 422);
    }

    public static function connectionFailed(string $reason): self
    {
        return new self("Failed to establish database connection: {$reason}", 503);
    }

    public static function configNotFound(string $id): self
    {
        return new self("Connection configuration '{$id}' not found.", 404);
    }
}
