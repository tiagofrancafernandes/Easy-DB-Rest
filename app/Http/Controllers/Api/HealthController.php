<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

class HealthController
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'status'          => 'ok',
            'service'         => 'easy-db-rest',
            'version'         => '1.0.0',
            'message'         => 'Easy DB Rest API is running',
            'datetime'        => now()->toDateTimeString(),
            'php_version'     => PHP_VERSION,
            'laravel_version' => app()->version(),
        ]);
    }
}
