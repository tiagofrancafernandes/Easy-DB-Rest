<?php

declare(strict_types=1);

namespace App\Services;

use App\DTOs\ConnectionConfigDto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TableDataService
{
    public function getPaginatedData(ConnectionConfigDto $config, string $table, int $limit = 50, int $offset = 0, array $options = []): array
    {
        $connection = $this->getConnection($config);
        $query = $connection->table($table);

        if (isset($options['schema'])) {
            $query = $connection->table($options['schema'] . '.' . $table);
        }

        $total = $query->count();
        $data = $query->limit($limit)->offset($offset)->get();

        return [
            'data' => $data,
            'meta' => [
                'total' => $total,
                'limit' => $limit,
                'offset' => $offset,
            ]
        ];
    }

    public function insertRecord(ConnectionConfigDto $config, string $table, array $data, array $options = []): bool
    {
        $connection = $this->getConnection($config);
        $tableName = isset($options['schema']) ? $options['schema'] . '.' . $table : $table;
        
        return $connection->table($tableName)->insert($data);
    }

    public function updateRecord(ConnectionConfigDto $config, string $table, array $pk, array $data, array $options = []): bool
    {
        $connection = $this->getConnection($config);
        $tableName = isset($options['schema']) ? $options['schema'] . '.' . $table : $table;
        
        return (bool) $connection->table($tableName)->where($pk)->update($data);
    }

    public function deleteRecord(ConnectionConfigDto $config, string $table, array $pk, array $options = []): bool
    {
        $connection = $this->getConnection($config);
        $tableName = isset($options['schema']) ? $options['schema'] . '.' . $table : $table;
        
        return (bool) $connection->table($tableName)->where($pk)->delete();
    }

    private function getConnection(ConnectionConfigDto $config): \Illuminate\Database\Connection
    {
        $connectionName = 'dynamic_data_' . Str::uuid()->toString();
        config(["database.connections.{$connectionName}" => $config->toLaravelConfig()]);
        return DB::connection($connectionName);
    }
}
