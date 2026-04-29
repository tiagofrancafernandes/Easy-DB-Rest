<?php

namespace Database\Factories;

use App\Enums\SnippetType;
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
            'name' => implode('-', fake()->words(2)) . '.sql',
            'type' => SnippetType::SQL,
            'content' => 'SELECT * FROM users;',
            'public_content_slug' => fn (array $data) => strval(
                str($data['name'] ?? '')->beforeLast('.')->slug() ?: fake()->slug()
            ),
            'public_content_index' => false,
        ];
    }
}
