<?php

declare(strict_types=1);

return [
    // =========================
    // PostgreSQL - Observabilidade
    // =========================
    [
        'name' => 'pg-active-queries.sql',
        'type' => 'pgsql',
        'content' => <<<'SQL'
SELECT pid, usename, state, query, now() - query_start AS duration
FROM pg_stat_activity
WHERE state <> 'idle'
ORDER BY duration DESC;
SQL,
        'public_content_slug' => 'pg-active-queries',
        'public_content_index' => false,
    ],

    [
        'name' => 'pg-long-running-queries.sql',
        'type' => 'pgsql',
        'content' => <<<'SQL'
SELECT pid, usename, now() - query_start AS duration, query
FROM pg_stat_activity
WHERE now() - query_start > interval '5 minutes'
ORDER BY duration DESC;
SQL,
        'public_content_slug' => 'pg-long-running-queries',
        'public_content_index' => false,
    ],

    [
        'name' => 'pg-locks.sql',
        'type' => 'pgsql',
        'content' => <<<'SQL'
SELECT blocked_locks.pid     AS blocked_pid,
       blocking_locks.pid    AS blocking_pid,
       blocked_activity.query AS blocked_query,
       blocking_activity.query AS blocking_query
FROM pg_locks blocked_locks
JOIN pg_stat_activity blocked_activity
  ON blocked_activity.pid = blocked_locks.pid
JOIN pg_locks blocking_locks
  ON blocking_locks.locktype = blocked_locks.locktype
 AND blocking_locks.database IS NOT DISTINCT FROM blocked_locks.database
 AND blocking_locks.relation IS NOT DISTINCT FROM blocked_locks.relation
 AND blocking_locks.pid != blocked_locks.pid
JOIN pg_stat_activity blocking_activity
  ON blocking_activity.pid = blocking_locks.pid
WHERE NOT blocked_locks.granted;
SQL,
        'public_content_slug' => 'pg-locks',
        'public_content_index' => false,
    ],

    [
        'name' => 'pg-db-size.sql',
        'type' => 'pgsql',
        'content' => <<<'SQL'
SELECT datname,
       pg_size_pretty(pg_database_size(datname)) AS size
FROM pg_database
ORDER BY pg_database_size(datname) DESC;
SQL,
        'public_content_slug' => 'pg-db-size',
        'public_content_index' => false,
    ],

    // =========================
    // PostgreSQL - Controle
    // =========================
    [
        'name' => 'pg-terminate-backend.sql',
        'type' => 'pgsql',
        'content' => <<<'SQL'
SELECT pg_terminate_backend(pid)
FROM pg_stat_activity
WHERE pid <> pg_backend_pid()
  AND state = 'idle';
SQL,
        'public_content_slug' => 'pg-terminate-backend',
        'public_content_index' => false,
    ],

    [
        'name' => 'pg-cancel-query.sql',
        'type' => 'pgsql',
        'content' => <<<'SQL'
SELECT pg_cancel_backend(pid)
FROM pg_stat_activity
WHERE state = 'active';
SQL,
        'public_content_slug' => 'pg-cancel-query',
        'public_content_index' => false,
    ],

    // =========================
    // PostgreSQL - Estrutura
    // =========================
    [
        'name' => 'pg-list-tables.sql',
        'type' => 'pgsql',
        'content' => <<<'SQL'
SELECT schemaname, tablename
FROM pg_tables
WHERE schemaname NOT IN ('pg_catalog', 'information_schema')
ORDER BY schemaname, tablename;
SQL,
        'public_content_slug' => 'pg-list-tables',
        'public_content_index' => false,
    ],

    [
        'name' => 'pg-table-sizes.sql',
        'type' => 'pgsql',
        'content' => <<<'SQL'
SELECT relname AS table,
       pg_size_pretty(pg_total_relation_size(relid)) AS total_size
FROM pg_catalog.pg_statio_user_tables
ORDER BY pg_total_relation_size(relid) DESC;
SQL,
        'public_content_slug' => 'pg-table-sizes',
        'public_content_index' => false,
    ],

    // =========================
    // Laravel / PHP - DB
    // =========================
    [
        'name' => 'laravel-db-select.php',
        'type' => 'php',
        'content' => <<<'PHP'
use Illuminate\Support\Facades\DB;

$users = DB::select('SELECT * FROM users WHERE active = ?', [1]);

foreach ($users as $user) {
    echo $user->id . PHP_EOL;
}
PHP,
        'public_content_slug' => 'laravel-db-select',
        'public_content_index' => false,
    ],

    [
        'name' => 'laravel-db-transaction.php',
        'type' => 'php',
        'content' => <<<'PHP'
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    DB::table('accounts')->where('id', 1)->decrement('balance', 100);
    DB::table('accounts')->where('id', 2)->increment('balance', 100);
});
PHP,
        'public_content_slug' => 'laravel-db-transaction',
        'public_content_index' => false,
    ],

    // =========================
    // Laravel - Eloquent
    // =========================
    [
        'name' => 'eloquent-basic-query.php',
        'type' => 'php',
        'content' => <<<'PHP'
use App\Models\MyModel;

$items = MyModel::query()
    ->where('status', 'active')
    ->orderByDesc('id')
    ->limit(10)
    ->get();
PHP,
        'public_content_slug' => 'eloquent-basic-query',
        'public_content_index' => false,
    ],

    [
        'name' => 'eloquent-chunk.php',
        'type' => 'php',
        'content' => <<<'PHP'
use App\Models\MyModel;

MyModel::chunk(100, function ($items) {
    foreach ($items as $item) {
        // process
    }
});
PHP,
        'public_content_slug' => 'eloquent-chunk',
        'public_content_index' => false,
    ],

    [
        'name' => 'eloquent-upsert.php',
        'type' => 'php',
        'content' => <<<'PHP'
use App\Models\MyModel;

MyModel::upsert([
    ['email' => 'test@example.com', 'name' => 'Test'],
], ['email'], ['name']);
PHP,
        'public_content_slug' => 'eloquent-upsert',
        'public_content_index' => false,
    ],

    // =========================
    // Extras úteis
    // =========================
    [
        'name' => 'pg-current-connections.sql',
        'type' => 'pgsql',
        'content' => <<<'SQL'
SELECT count(*) AS total_connections
FROM pg_stat_activity;
SQL,
        'public_content_slug' => 'pg-current-connections',
        'public_content_index' => false,
    ],

    [
        'name' => 'pg-cache-hit-ratio.sql',
        'type' => 'pgsql',
        'content' => <<<'SQL'
SELECT sum(blks_hit) / (sum(blks_hit) + sum(blks_read)) AS cache_hit_ratio
FROM pg_stat_database;
SQL,
        'public_content_slug' => 'pg-cache-hit-ratio',
        'public_content_index' => false,
    ],
];
