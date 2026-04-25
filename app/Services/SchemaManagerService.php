<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ConnectionConfigDto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Database\Schema\Blueprint;

class SchemaManagerService
{
    /**
     * @param ConnectionConfigDto $config
     * @return array
     */
    public function listDatabases(ConnectionConfigDto $config): array
    {
        $connection = $this->getConnection($config);

        try {
            return $connection->getSchemaBuilder()->getDatabases();
        } catch (\Throwable $e) {
            // Fallback for drivers that don't support getDatabases in SchemaBuilder
            // or if it fails. For SQLite, it usually doesn't make sense.
            return [];
        }
    }

    /**
     * @param ConnectionConfigDto $config
     * @param string $name
     * @return void
     */
    public function createDatabase(ConnectionConfigDto $config, string $name): void
    {
        $connection = $this->getConnection($config);
        $connection->statement("CREATE DATABASE {$name}");
    }

    /**
     * @param ConnectionConfigDto $config
     * @param string $name
     * @return void
     */
    public function dropDatabase(ConnectionConfigDto $config, string $name): void
    {
        $connection = $this->getConnection($config);
        $connection->statement("DROP DATABASE {$name}");
    }

    /**
     * @param ConnectionConfigDto $config
     * @return array
     */
    public function listSchemas(ConnectionConfigDto $config): array
    {
        $connection = $this->getConnection($config);

        try {
            return $connection->getSchemaBuilder()->getSchemas();
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * @param ConnectionConfigDto $config
     * @param string $name
     * @return void
     */
    public function createSchema(ConnectionConfigDto $config, string $name): void
    {
        $connection = $this->getConnection($config);
        $connection->statement("CREATE SCHEMA {$name}");
    }

    /**
     * @param ConnectionConfigDto $config
     * @param string $name
     * @return void
     */
    public function dropSchema(ConnectionConfigDto $config, string $name): void
    {
        $connection = $this->getConnection($config);
        $connection->statement("DROP SCHEMA {$name}");
    }

    /**
     * @param ConnectionConfigDto $config
     * @return array
     */
    public function listTables(ConnectionConfigDto $config): array
    {
        $connection = $this->getConnection($config);

        return $connection->getSchemaBuilder()->getTables();
    }

    /**
     * @param ConnectionConfigDto $config
     * @param string $table
     * @return array
     */
    public function getTableDetails(ConnectionConfigDto $config, string $table): array
    {
        $schema = $this->getConnection($config)->getSchemaBuilder();

        return [
            'columns' => $schema->getColumns($table),
            'indexes' => $schema->getIndexes($table),
            'foreign_keys' => $schema->getForeignKeys($table),
        ];
    }

    /**
     * @param ConnectionConfigDto $config
     * @param string $table
     * @param array $columns
     * @return void
     */
    public function createTable(ConnectionConfigDto $config, string $table, array $columns): void
    {
        $schema = $this->getConnection($config)->getSchemaBuilder();

        $schema->create($table, function (Blueprint $table) use ($columns) {
            foreach ($columns as $column) {
                $type = $column['type'] ?? 'string';
                $name = $column['name'];
                $args = $column['args'] ?? [];

                $colObj = $table->{$type}($name, ...$args);

                if ($column['nullable'] ?? false) {
                    $colObj->nullable();
                }

                if (isset($column['default'])) {
                    $colObj->default($column['default']);
                }

                if ($column['index'] ?? false) {
                    $table->index($name);
                }
            }
        });
    }

    /**
     * @param ConnectionConfigDto $config
     * @param string $table
     * @param array $changes
     * @return void
     */
    public function alterTable(ConnectionConfigDto $config, string $table, array $changes): void
    {
        $schema = $this->getConnection($config)->getSchemaBuilder();

        $schema->table($table, function (Blueprint $table) use ($changes) {
            if (isset($changes['add'])) {
                foreach ($changes['add'] as $column) {
                    $type = $column['type'] ?? 'string';
                    $name = $column['name'];
                    $args = $column['args'] ?? [];
                    $colObj = $table->{$type}($name, ...$args);

                    if ($column['nullable'] ?? false) {
                        $colObj->nullable();
                    }
                }
            }

            if (isset($changes['drop'])) {
                foreach ($changes['drop'] as $columnName) {
                    $table->dropColumn($columnName);
                }
            }

            if (isset($changes['modify'])) {
                foreach ($changes['modify'] as $column) {
                    $type = $column['type'] ?? 'string';
                    $name = $column['name'];
                    $args = $column['args'] ?? [];
                    $table->{$type}($name, ...$args)->change();
                }
            }

            if (isset($changes['rename'])) {
                foreach ($changes['rename'] as $old => $new) {
                    $table->renameColumn($old, $new);
                }
            }
        });
    }

    /**
     * @param ConnectionConfigDto $config
     * @return array
     */
    public function listViews(ConnectionConfigDto $config): array
    {
        $connection = $this->getConnection($config);

        return $connection->getSchemaBuilder()->getViews();
    }

    /**
     * @param ConnectionConfigDto $config
     * @param string $name
     * @param string $query
     * @return void
     */
    public function createView(ConnectionConfigDto $config, string $name, string $query): void
    {
        $connection = $this->getConnection($config);
        $connection->statement("CREATE VIEW {$name} AS {$query}");
    }

    /**
     * @param ConnectionConfigDto $config
     * @param string $name
     * @return void
     */
    public function dropView(ConnectionConfigDto $config, string $name): void
    {
        $connection = $this->getConnection($config);
        $connection->statement("DROP VIEW IF EXISTS {$name}");
    }

    /**
     * @param ConnectionConfigDto $config
     * @param string $table
     * @return void
     */
    public function dropTable(ConnectionConfigDto $config, string $table): void
    {
        $this->getConnection($config)->getSchemaBuilder()->dropIfExists($table);
    }

    /**
     * @param ConnectionConfigDto $config
     * @return \Illuminate\Database\Connection
     */
    private function getConnection(ConnectionConfigDto $config): \Illuminate\Database\Connection
    {
        $connectionName = 'dynamic_' . Str::uuid()->toString();

        config(["database.connections.{$connectionName}" => $config->toLaravelConfig()]);

        return DB::connection($connectionName);
    }
}
