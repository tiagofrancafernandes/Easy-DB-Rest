<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;

class HomeController
{
    public function __invoke(Request $request)
    {
        $check = $request->has('check') || $request->boolean('check');

        $data = [
            'status' => 'ok',
            'service' => 'easy-db-rest',
            'version' => '1.0.0',
            'message' => 'Easy DB Rest API is running',
            'datetime' => now()->toDateTimeString(),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'routes' => [
                // TODO Atualizar exemplos de rotas
                [
                    'method' => 'POST',
                    'uri' => '/reset',
                    'description' => 'Reset state',
                ],
                [
                    'method' => 'POST',
                    'uri' => '/config',
                    'description' => 'Create DB connection config',
                ],
                [
                    'method' => 'GET',
                    'uri' => '/config?driver={driver}',
                    'description' => 'Get DB connection config',
                    'example' => '/config?driver=pgsql',
                ],
                [
                    'method' => 'POST',
                    'uri' => '/query',
                    'description' => 'Run query',
                    'examples' => [
                        // ...
                    ],
                ],
            ],
        ];

        if ($check) {
            $data['health'] = 'ok';
        }

        return response()->json($data);
    }
}
