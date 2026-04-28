<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

class HttpHelpers
{
    public static function jsonError(string $message, string $code = 'UNKNOWN_ERROR', null|int $status = null, mixed $details = null): JsonResponse
    {
        return new JsonResponse([
            'error'   => true,
            'message' => $message,
            'code'    => $code ?: 'UNKNOWN_ERROR',
            'details' => $details ?? (object) [],
        ], $status ?? 422);
    }
}
