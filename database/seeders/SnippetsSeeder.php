<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Snippet;
use App\Helpers\SnippetHelpers;
use App\Models\User;
use Illuminate\Database\Seeder;

class SnippetsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = $this->getOrCreateAdminUser();

        $allSnippets = $this->collectAllSnippets();

        foreach ($allSnippets as $snippet) {
            $snippet = SnippetHelpers::prepareToInsert($snippet, user: $admin)->toArray();

            Snippet::updateOrCreate(
                [
                    'user_id' => $admin->id,
                    'name' => $snippet['name'],
                ],
                [
                    'user_id' => $admin->id,
                    'name' => $snippet['name'],
                    'type' => $snippet['type'],
                    'content' => $snippet['content'],
                    'public_content_slug' => $snippet['public_content_slug'] ?? null,
                    'public_content_password' => $snippet['public_content_password'] ?? null,
                    'public_content_index' => $snippet['public_content_index'] ?? false,
                ]
            );
        }

        $this->command->info('Snippets seeded successfully for admin@mail.com');
    }

    /**
     * Get or create admin user.
     */
    private function getOrCreateAdminUser(): User
    {
        $admin = User::firstWhere('email', 'admin@mail.com');

        if ($admin) {
            return $admin;
        }

        $this->command->info('Admin user not found. Running AdminUserSeeder...');

        $this->call(AdminUserSeeder::class);

        $admin = User::firstWhere('email', 'admin@mail.com');

        if (! $admin) {
            throw new \RuntimeException('Failed to create admin user');
        }

        return $admin;
    }

    /**
     * Collect all snippets from static data files.
     *
     * @return array<int, array<string, mixed>>
     */
    private function collectAllSnippets(): array
    {
        $snippets = [];

        $dataFiles = [
            base_path('database/static-data/snippets/01-postgres-and-laravel.php'),
            base_path('database/static-data/snippets/02-laravel-advanced.php'),
            base_path('database/static-data/snippets/03-javascript-typescript.php'),
            base_path('database/static-data/snippets/04-shell-commands.php'),
            base_path('database/static-data/snippets/05-markdown-detailed-guides.php'),
            base_path('database/static-data/snippets/06-mysql-mariadb.php'),
            base_path('database/static-data/snippets/07-mysql-useful-queries-reference.php'),
        ];

        foreach ($dataFiles as $file) {
            if (! file_exists($file)) {
                $this->command->warn("Snippet file not found: {$file}");

                continue;
            }

            $fileSnippets = require $file;

            if (! is_array($fileSnippets)) {
                $this->command->warn("Invalid snippet file format: {$file}");

                continue;
            }

            $snippets = array_merge($snippets, $fileSnippets);
        }

        return $snippets;
    }
}
