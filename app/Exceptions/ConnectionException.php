<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class ConnectionException extends RuntimeException
{
    public static function driverNotAllowed(string $driver): static
    {
        return new static("Driver '{$driver}' is not in the allowed drivers list.", 422);
    }

    public static function connectionFailed(string $reason): static
    {
        return new static("Failed to establish database connection: {$reason}", 503);
    }

    public static function configNotFound(string $id): static
    {
        return new static("Connection configuration '{$id}' not found.", 404);
    }
}
