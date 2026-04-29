<?php

declare(strict_types=1);

return [
    // =========================
    // PostgreSQL - Detailed Guides
    // =========================
    [
        'name' => 'pg-list-all-databases',
        'type' => 'markdown',
        'content' => <<<'MD'
# List All Databases in PostgreSQL

## Method 1: Database Names Only
```sql
SELECT datname
FROM pg_database
WHERE datistemplate = false
ORDER BY datname;
```

## Method 2: Advanced Detail with Owner
```sql
SELECT
    datname,
    pg_get_userbyid(datdba) AS owner,
    encoding,
    datcollate,
    pg_size_pretty(pg_database_size(datname)) AS size
FROM pg_catalog.pg_database
WHERE datistemplate = false
ORDER BY datname;
```

## Method 3: Connection Count and Activity
```sql
SELECT
    d.datname,
    pg_get_userbyid(d.datdba) AS owner,
    count(psa.pid) AS connections,
    (SELECT count(*) FROM pg_stat_activity WHERE datname = d.datname AND state = 'active') AS active_queries
FROM pg_database d
LEFT JOIN pg_stat_activity psa ON d.datname = psa.datname
WHERE d.datistemplate = false
GROUP BY d.datname, d.datdba
ORDER BY connections DESC;
```

## Using psql CLI
```bash
\l              # List databases
\l+             # List with sizes
\l+ db_name     # Specific database
```
MD,
        'public_content_slug' => 'pg-list-all-databases',
        'public_content_index' => false,
    ],

    [
        'name' => 'pg-table-statistics-guide',
        'type' => 'markdown',
        'content' => <<<'MD'
# PostgreSQL Table Statistics & Optimization

## Get Table Sizes
```sql
SELECT
    schemaname,
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) AS total_size,
    pg_size_pretty(pg_relation_size(schemaname||'.'||tablename)) AS table_size,
    pg_size_pretty(pg_indexes_size(schemaname||'.'||tablename)) AS indexes_size
FROM pg_tables
WHERE schemaname NOT IN ('pg_catalog', 'information_schema')
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;
```

## Table Statistics
```sql
SELECT
    schemaname,
    tablename,
    n_live_tup AS row_count,
    n_dead_tup AS dead_rows,
    last_vacuum,
    last_autovacuum,
    vacuum_count,
    autovacuum_count
FROM pg_stat_user_tables
ORDER BY n_live_tup DESC;
```

## Index Information
```sql
SELECT
    schemaname,
    tablename,
    indexname,
    indexdef
FROM pg_indexes
WHERE schemaname NOT IN ('pg_catalog', 'information_schema')
ORDER BY schemaname, tablename;
```

## Unused Indexes
```sql
SELECT
    schemaname,
    tablename,
    indexname,
    idx_scan
FROM pg_stat_user_indexes
WHERE idx_scan = 0
ORDER BY schemaname, tablename;
```
MD,
        'public_content_slug' => 'pg-table-statistics-guide',
        'public_content_index' => false,
    ],

    [
        'name' => 'pg-performance-tuning-guide',
        'type' => 'markdown',
        'content' => <<<'MD'
# PostgreSQL Performance Tuning Guide

## Check Cache Hit Ratio
```sql
SELECT
    sum(heap_blks_read) AS heap_read,
    sum(heap_blks_hit) AS heap_hit,
    sum(heap_blks_hit) / (sum(heap_blks_hit) + sum(heap_blks_read)) AS ratio
FROM pg_statio_user_tables;
```

## Check Index Usage
```sql
SELECT
    schemaname,
    tablename,
    indexname,
    idx_scan,
    idx_tup_read,
    idx_tup_fetch
FROM pg_stat_user_indexes
ORDER BY idx_scan DESC;
```

## Slow Queries
```sql
SELECT
    query,
    mean_exec_time,
    max_exec_time,
    stddev_exec_time,
    calls
FROM pg_stat_statements
WHERE mean_exec_time > 100
ORDER BY mean_exec_time DESC
LIMIT 10;
```

## Connection Limits
```sql
SELECT
    datname,
    numbackends,
    max_conn,
    ROUND(100.0 * numbackends / max_conn, 1) AS conn_pct
FROM (
    SELECT datname, count(*) AS numbackends, setting::int AS max_conn
    FROM pg_stat_activity, pg_settings
    WHERE pg_settings.name = 'max_connections'
    GROUP BY datname, max_conn
) t
ORDER BY numbackends DESC;
```

## Autovacuum Status
```sql
SELECT
    schemaname,
    tablename,
    last_autovacuum,
    last_autoanalyze,
    autovacuum_count,
    autoanalyze_count
FROM pg_stat_user_tables
ORDER BY last_autovacuum DESC NULLS LAST;
```
MD,
        'public_content_slug' => 'pg-performance-tuning-guide',
        'public_content_index' => false,
    ],

    [
        'name' => 'pg-backup-restore-guide',
        'type' => 'markdown',
        'content' => <<<'MD'
# PostgreSQL Backup & Restore Complete Guide

## Backup Methods

### 1. Full Database Backup (SQL Format)
```bash
pg_dump -U postgres -d database_name -f backup.sql
```

### 2. Full Database Backup (Custom Format)
```bash
pg_dump -U postgres -d database_name -Fc -f backup.dump
```

### 3. All Databases Backup
```bash
pg_dumpall -U postgres -f all_databases.sql
```

### 4. Compressed Backup
```bash
pg_dump -U postgres -d database_name | gzip > backup.sql.gz
```

## Restore Methods

### 1. Restore from SQL File
```bash
psql -U postgres -d database_name -f backup.sql
```

### 2. Restore from Custom Format
```bash
pg_restore -U postgres -d database_name backup.dump
```

### 3. Restore with Verbose Output
```bash
pg_restore -U postgres -d database_name -v backup.dump
```

## Backup Strategies

### Incremental Backup (WAL Archiving)
```bash
# Enable WAL archiving in postgresql.conf
# wal_level = replica
# archive_mode = on
# archive_command = 'cp %p /backup/wal_archive/%f'

# List WAL files
ls -la /backup/wal_archive/
```

### Continuous Archiving
```bash
# Create backup directory
mkdir -p /backup/pg_basebackup

# Take base backup
pg_basebackup -D /backup/pg_basebackup -Ft -z -P
```
MD,
        'public_content_slug' => 'pg-backup-restore-guide',
        'public_content_index' => false,
    ],

    // =========================
    // Laravel - Detailed Guides
    // =========================
    [
        'name' => 'laravel-migration-best-practices',
        'type' => 'markdown',
        'content' => <<<'MD'
# Laravel Migrations - Best Practices Guide

## Migration File Structure
```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->rememberToken();
    $table->timestamps();
    $table->softDeletes();
});
```

## Common Column Types
```php
$table->bigIncrements('id');           // Auto-incrementing BIGINT
$table->string('name', 100);           // VARCHAR with length
$table->text('description');           // TEXT column
$table->integer('count')->default(0);  // INTEGER with default
$table->decimal('price', 8, 2);        // DECIMAL(8,2)
$table->boolean('active');             // BOOLEAN
$table->date('birth_date');            // DATE
$table->dateTime('created_at');        // DATETIME
$table->timestamp('updated_at');       // TIMESTAMP
$table->json('metadata');              // JSON
$table->uuid('id');                    // UUID
```

## Indexes and Constraints
```php
$table->unique('email');               // Unique constraint
$table->unique(['email', 'account_id']); // Composite unique
$table->index('status');               // Index
$table->fullText('description');       // Full-text index
$table->foreign('user_id')
    ->references('id')
    ->on('users')
    ->onDelete('cascade');
```

## Modifying Columns
```php
Schema::table('users', function (Blueprint $table) {
    $table->string('email', 100)->change();         // Change column
    $table->string('phone')->nullable()->change();  // Add nullable
    $table->renameColumn('old_name', 'new_name');   // Rename
    $table->dropColumn('phone');                    // Drop column
    $table->dropUnique(['email']);                  // Drop unique
    $table->dropForeign(['user_id']);               // Drop foreign key
});
```

## Useful Commands
```bash
php artisan migrate                    # Run migrations
php artisan migrate --seed             # Run with seeders
php artisan migrate:rollback           # Rollback last batch
php artisan migrate:refresh            # Rollback all & re-run
php artisan migrate:fresh              # Drop all & re-run
php artisan make:migration create_table_name  # Create migration
```
MD,
        'public_content_slug' => 'laravel-migration-best-practices',
        'public_content_index' => false,
    ],

    [
        'name' => 'laravel-eloquent-relationships-guide',
        'type' => 'markdown',
        'content' => <<<'MD'
# Laravel Eloquent Relationships Guide

## One-to-Many (1:N)
```php
// User has many Posts
class User extends Model {
    public function posts() {
        return $this->hasMany(Post::class);
    }
}

// Post belongs to User
class Post extends Model {
    public function user() {
        return $this->belongsTo(User::class);
    }
}

// Usage
$user = User::find(1);
$posts = $user->posts;       // Get all posts
$post = Post::find(1);
$user = $post->user;         // Get post author
```

## Many-to-Many (N:N)
```php
// User has many Roles
class User extends Model {
    public function roles() {
        return $this->belongsToMany(Role::class, 'role_user');
    }
}

// Role has many Users
class Role extends Model {
    public function users() {
        return $this->belongsToMany(User::class, 'role_user');
    }
}

// Usage
$user = User::find(1);
$user->roles()->attach($roleId);       // Attach role
$user->roles()->detach($roleId);       // Remove role
$user->roles()->sync([$role1, $role2]); // Sync roles
```

## Has-Many-Through
```php
// User has many Comments (through Posts)
class User extends Model {
    public function comments() {
        return $this->hasManyThrough(Comment::class, Post::class);
    }
}

// Usage
$user = User::find(1);
$comments = $user->comments;  // All comments on user's posts
```

## Polymorphic Relations
```php
// Comment can belong to Post or Video
class Comment extends Model {
    public function commentable() {
        return $this->morphTo();
    }
}

class Post extends Model {
    public function comments() {
        return $this->morphMany(Comment::class, 'commentable');
    }
}

// Usage
$post->comments()->create(['content' => 'Great post!']);
$comment = Comment::find(1);
$post = $comment->commentable;  // Get the post
```
MD,
        'public_content_slug' => 'laravel-eloquent-relationships-guide',
        'public_content_index' => false,
    ],

    [
        'name' => 'rest-api-design-best-practices',
        'type' => 'markdown',
        'content' => <<<'MD'
# REST API Design Best Practices

## Resource Naming Conventions
```
GET    /api/users              # List users
GET    /api/users/1            # Get specific user
POST   /api/users              # Create user
PUT    /api/users/1            # Update user
DELETE /api/users/1            # Delete user

GET    /api/users/1/posts      # User's posts (nested)
GET    /api/posts?user_id=1    # Filter by user
```

## HTTP Status Codes
```
200 OK                    # Request successful
201 Created               # Resource created
204 No Content            # Successful, no response body
400 Bad Request           # Invalid request
401 Unauthorized          # Authentication required
403 Forbidden             # Not authorized
404 Not Found             # Resource not found
409 Conflict              # Resource conflict
422 Unprocessable Entity  # Validation error
500 Internal Server Error # Server error
503 Service Unavailable   # Service down
```

## Response Format
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "status": 200,
  "message": "User retrieved successfully"
}
```

## Error Response Format
```json
{
  "status": 422,
  "message": "Validation error",
  "errors": {
    "email": ["The email field is required"],
    "password": ["Password must be at least 8 characters"]
  }
}
```

## Pagination
```json
{
  "data": [...],
  "pagination": {
    "current_page": 1,
    "per_page": 15,
    "total": 100,
    "last_page": 7
  }
}
```

## Best Practices
- Use consistent naming (snake_case or camelCase)
- Version your API (/api/v1/)
- Use Content-Type: application/json
- Support filtering, sorting, pagination
- Document all endpoints
- Use appropriate HTTP methods
- Return meaningful error messages
MD,
        'public_content_slug' => 'rest-api-design-best-practices',
        'public_content_index' => false,
    ],
];
