<?php

declare(strict_types=1);

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QuerySecurityTest extends TestCase
{
    protected function getRuntimeConfig(): array
    {
        return [
            'name'     => 'Runtime DB',
            'driver'   => 'sqlite',
            'database' => database_path('database.sqlite'),
        ];
    }

    #[Test]
    public function itBlocksDangerousRawQueries(): void
    {
        $response = $this->postJson('/api/query', [
            'type' => 'raw',
            'query' => 'DROP TABLE products',
            'connection' => $this->getRuntimeConfig()
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error', true)
            ->assertJsonPath('code', 'QUERY_SECURITY_ERROR');
    }

    #[Test]
    public function itBlocksForbiddenBuilderMethods(): void
    {
        $response = $this->postJson('/api/query', [
            'type' => 'select',
            'table' => 'products',
            'connection' => $this->getRuntimeConfig(),
            'query' => [
                [
                    'method' => 'delete',
                    'args' => []
                ]
            ]
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('error', true)
            ->assertJsonPath('code', 'QUERY_SECURITY_ERROR');
    }
}
