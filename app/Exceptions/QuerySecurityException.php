<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

class QuerySecurityException extends RuntimeException
{
    public static function blockedStatement(string $statement): static
    {
        return new static("Statement '{$statement}' is blocked by the security policy.", 422);
    }

    public static function forbiddenBuilderMethod(string $method): static
    {
        return new static("Builder method '{$method}' is not in the allowed methods list.", 422);
    }
}
