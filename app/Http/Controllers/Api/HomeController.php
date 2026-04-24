<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

class HomeController
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
            'routes'          => [
                ['method' => 'GET', 'uri' => '/', 'description' => 'API info (this response)'],
                ['method' => 'GET', 'uri' => '/health', 'description' => 'Health check'],
                ['method' => 'POST', 'uri' => '/connection/test', 'description' => 'Test a connection (stored or inline)'],
                ['method' => 'GET', 'uri' => '/connections', 'description' => 'List stored connection configs'],
                ['method' => 'POST', 'uri' => '/connections', 'description' => 'Create a stored connection config'],
                ['method' => 'GET', 'uri' => '/connections/{id}', 'description' => 'Get a stored connection config'],
                ['method' => 'PUT', 'uri' => '/connections/{id}', 'description' => 'Update a stored connection config'],
                ['method' => 'DELETE', 'uri' => '/connections/{id}', 'description' => 'Delete a stored connection config'],
                ['method' => 'POST', 'uri' => '/query', 'description' => 'Execute a query (auto-detects SQL content-type or JSON)'],
                ['method' => 'POST', 'uri' => '/query/raw', 'description' => 'Execute raw SQL (body = SQL text)'],
                ['method' => 'POST', 'uri' => '/query/builder', 'description' => 'Execute a declarative JSON query builder payload'],
            ],
        ]);
    }
}
