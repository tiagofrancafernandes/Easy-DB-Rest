<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Connection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Connection>
 */
class ConnectionFactory extends Factory
{
    protected $model = Connection::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'  => \App\Models\User::factory(),
            'name'     => $this->faker->words(2, asText: true),
            'driver'   => 'sqlite',
            'database' => database_path('database.sqlite'),
            'host'     => null,
            'port'     => null,
            'username' => null,
            'password' => null,
            'schema'   => null,
            'timeout'  => null,
            'options'  => null,
        ];
    }

    public function pgsql(): static
    {
        return $this->state([
            'driver'   => 'pgsql',
            'host'     => '127.0.0.1',
            'port'     => 5432,
            'database' => 'testdb',
            'username' => 'postgres',
        ]);
    }
}
