<?php

namespace Database\Factories;

use App\Models\Snippet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Snippet>
 */
class SnippetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->word() . '.sql',
            'type' => 'sql',
            'content' => 'SELECT * FROM users;',
            'public_content_slug' => $this->faker->slug(),
            'public_content_index' => false,
        ];
    }
}
