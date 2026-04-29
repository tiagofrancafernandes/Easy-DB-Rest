<?php

declare(strict_types=1);

return [
    // =========================
    // MySQL - Observabilidade
    // =========================
    [
        'name' => 'mysql-current-processes.sql',
        'type' => 'mysql',
        'content' => <<<'SQL'
SELECT ID, USER, HOST, DB, COMMAND, TIME, STATE, INFO
FROM INFORMATION_SCHEMA.PROCESSLIST
WHERE COMMAND != 'Sleep'
ORDER BY TIME DESC;
SQL,
        'public_content_slug' => 'mysql-current-processes',
        'public_content_index' => false,
    ],

    [
        'name' => 'mysql-long-running-queries.sql',
        'type' => 'mysql',
        'content' => <<<'SQL'
SELECT ID, USER, HOST, DB, COMMAND, TIME, STATE, INFO
FROM INFORMATION_SCHEMA.PROCESSLIST
WHERE TIME > 300 AND COMMAND != 'Sleep'
ORDER BY TIME DESC;
SQL,
        'public_content_slug' => 'mysql-long-running-queries',
        'public_content_index' => false,
    ],

    [
        'name' => 'mysql-database-size.sql',
        'type' => 'mysql',
        'content' => <<<'SQL'
SELECT
    SCHEMA_NAME,
    ROUND(SUM(DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS SIZE_MB
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA NOT IN ('mysql', 'information_schema', 'performance_schema')
GROUP BY SCHEMA_NAME
ORDER BY SUM(DATA_LENGTH + INDEX_LENGTH) DESC;
SQL,
        'public_content_slug' => 'mysql-database-size',
        'public_content_index' => false,
    ],

    [
        'name' => 'mysql-table-sizes.sql',
        'type' => 'mysql',
        'content' => <<<'SQL'
SELECT
    TABLE_SCHEMA,
    TABLE_NAME,
    ROUND(((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2) AS SIZE_MB,
    TABLE_ROWS
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA NOT IN ('mysql', 'information_schema', 'performance_schema')
ORDER BY (DATA_LENGTH + INDEX_LENGTH) DESC;
SQL,
        'public_content_slug' => 'mysql-table-sizes',
        'public_content_index' => false,
    ],

    [
        'name' => 'mysql-kill-process.sql',
        'type' => 'mysql',
        'content' => <<<'SQL'
-- Kill a specific process by ID
KILL QUERY 123;

-- Or kill entire connection
KILL CONNECTION 123;

-- Kill all processes from specific user (run multiple times)
SELECT CONCAT('KILL ', ID, ';') FROM INFORMATION_SCHEMA.PROCESSLIST
WHERE USER = 'username' AND ID != CONNECTION_ID();
SQL,
        'public_content_slug' => 'mysql-kill-process',
        'public_content_index' => false,
    ],

    [
        'name' => 'mysql-list-tables.sql',
        'type' => 'mysql',
        'content' => <<<'SQL'
SELECT TABLE_SCHEMA, TABLE_NAME, TABLE_TYPE
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA NOT IN ('mysql', 'information_schema', 'performance_schema')
ORDER BY TABLE_SCHEMA, TABLE_NAME;
SQL,
        'public_content_slug' => 'mysql-list-tables',
        'public_content_index' => false,
    ],

    [
        'name' => 'mysql-table-columns.sql',
        'type' => 'mysql',
        'content' => <<<'SQL'
SELECT
    TABLE_SCHEMA,
    TABLE_NAME,
    COLUMN_NAME,
    COLUMN_TYPE,
    IS_NULLABLE,
    COLUMN_KEY,
    COLUMN_DEFAULT
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = 'your_database'
AND TABLE_NAME = 'your_table'
ORDER BY ORDINAL_POSITION;
SQL,
        'public_content_slug' => 'mysql-table-columns',
        'public_content_index' => false,
    ],

    [
        'name' => 'mysql-indexes-info.sql',
        'type' => 'mysql',
        'content' => <<<'SQL'
SELECT
    TABLE_SCHEMA,
    TABLE_NAME,
    INDEX_NAME,
    COLUMN_NAME,
    SEQ_IN_INDEX,
    NON_UNIQUE
FROM INFORMATION_SCHEMA.STATISTICS
WHERE TABLE_SCHEMA NOT IN ('mysql', 'information_schema', 'performance_schema')
ORDER BY TABLE_SCHEMA, TABLE_NAME, INDEX_NAME;
SQL,
        'public_content_slug' => 'mysql-indexes-info',
        'public_content_index' => false,
    ],

    // =========================
    // MySQL - Performance
    // =========================
    [
        'name' => 'mysql-slow-log-enable.sql',
        'type' => 'mysql',
        'content' => <<<'SQL'
-- Enable slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;

-- View slow log settings
SHOW VARIABLES LIKE 'slow_query%';
SHOW VARIABLES LIKE 'long_query_time';

-- Check log location
SHOW VARIABLES LIKE 'slow_query_log_file';
SQL,
        'public_content_slug' => 'mysql-slow-log-enable',
        'public_content_index' => false,
    ],

    [
        'name' => 'mysql-table-statistics.sql',
        'type' => 'mysql',
        'content' => <<<'SQL'
SELECT
    OBJECT_SCHEMA,
    OBJECT_NAME,
    COUNT_READ,
    COUNT_WRITE,
    COUNT_INSERT,
    COUNT_UPDATE,
    COUNT_DELETE
FROM performance_schema.table_io_waits_summary_by_table
WHERE OBJECT_SCHEMA NOT IN ('mysql', 'information_schema', 'performance_schema')
ORDER BY COUNT_READ + COUNT_WRITE DESC;
SQL,
        'public_content_slug' => 'mysql-table-statistics',
        'public_content_index' => false,
    ],

    [
        'name' => 'mysql-wait-events.sql',
        'type' => 'mysql',
        'content' => <<<'SQL'
SELECT
    EVENT_NAME,
    COUNT_STAR,
    SUM_TIMER_WAIT / 1000000000000 AS TOTAL_WAIT_SEC,
    AVG_TIMER_WAIT / 1000000000 AS AVG_WAIT_MS
FROM performance_schema.events_waits_summary_global_by_event_name
WHERE COUNT_STAR > 0
ORDER BY SUM_TIMER_WAIT DESC
LIMIT 20;
SQL,
        'public_content_slug' => 'mysql-wait-events',
        'public_content_index' => false,
    ],

    // =========================
    // MySQL - Users & Permissions
    // =========================
    [
        'name' => 'mysql-list-users.sql',
        'type' => 'mysql',
        'content' => <<<'SQL'
SELECT USER, HOST, authentication_string, password_expired
FROM mysql.user
ORDER BY USER, HOST;
SQL,
        'public_content_slug' => 'mysql-list-users',
        'public_content_index' => false,
    ],

    [
        'name' => 'mysql-user-permissions.sql',
        'type' => 'mysql',
        'content' => <<<'SQL'
SHOW GRANTS FOR 'username'@'hostname';

-- Or get all user grants
SELECT CONCAT('SHOW GRANTS FOR ''', user, '''@''', host, ''';')
FROM mysql.user;
SQL,
        'public_content_slug' => 'mysql-user-permissions',
        'public_content_index' => false,
    ],

    [
        'name' => 'mysql-create-user.sql',
        'type' => 'mysql',
        'content' => <<<'SQL'
-- Create user
CREATE USER 'username'@'localhost' IDENTIFIED BY 'password';

-- Grant privileges
GRANT SELECT, INSERT, UPDATE, DELETE ON database_name.* TO 'username'@'localhost';

-- Grant all privileges
GRANT ALL PRIVILEGES ON database_name.* TO 'username'@'localhost';

-- Apply changes
FLUSH PRIVILEGES;
SQL,
        'public_content_slug' => 'mysql-create-user',
        'public_content_index' => false,
    ],

    // =========================
    // MariaDB - Specific Features
    // =========================
    [
        'name' => 'mariadb-version-info.sql',
        'type' => 'mysql',
        'content' => <<<'SQL'
-- Check MariaDB version
SELECT VERSION();

-- More detailed info
SHOW STATUS LIKE 'version%';

-- Check if running as MariaDB
SELECT @@version_comment;
SQL,
        'public_content_slug' => 'mariadb-version-info',
        'public_content_index' => false,
    ],

    [
        'name' => 'mariadb-virtual-columns.sql',
        'type' => 'mysql',
        'content' => <<<'SQL'
-- Create table with virtual column (MariaDB)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    full_name VARCHAR(100) GENERATED ALWAYS AS
        (CONCAT(first_name, ' ', last_name)) STORED
);

-- Insert data
INSERT INTO users (first_name, last_name) VALUES ('John', 'Doe');

-- Full name is automatically computed
SELECT * FROM users;
SQL,
        'public_content_slug' => 'mariadb-virtual-columns',
        'public_content_index' => false,
    ],

    [
        'name' => 'mariadb-common-table-expressions.sql',
        'type' => 'mysql',
        'content' => <<<'SQL'
-- Common Table Expression (CTE) - MariaDB 10.2+
WITH RECURSIVE numbers AS (
    SELECT 1 AS n
    UNION ALL
    SELECT n + 1 FROM numbers WHERE n < 10
)
SELECT * FROM numbers;

-- CTE with actual data
WITH user_orders AS (
    SELECT user_id, COUNT(*) as order_count
    FROM orders
    GROUP BY user_id
)
SELECT u.name, uo.order_count
FROM users u
JOIN user_orders uo ON u.id = uo.user_id;
SQL,
        'public_content_slug' => 'mariadb-common-table-expressions',
        'public_content_index' => false,
    ],

    [
        'name' => 'mariadb-json-functions.sql',
        'type' => 'mysql',
        'content' => <<<'SQL'
-- JSON functions (MariaDB)
SELECT JSON_OBJECT('name', 'John', 'age', 30) AS json_data;

-- Extract from JSON
SELECT
    JSON_EXTRACT('{"name":"John","age":30}', '$.name') AS name,
    JSON_EXTRACT('{"name":"John","age":30}', '$.age') AS age;

-- Query JSON columns
SELECT
    id,
    JSON_EXTRACT(metadata, '$.status') AS status,
    JSON_EXTRACT(metadata, '$.tags') AS tags
FROM users
WHERE JSON_EXTRACT(metadata, '$.status') = 'active';

-- JSON_SET and JSON_REPLACE
SELECT JSON_SET('{"a":1}', '$.b', 2) AS result;
SELECT JSON_REPLACE('{"a":1,"b":2}', '$.a', 99) AS result;
SQL,
        'public_content_slug' => 'mariadb-json-functions',
        'public_content_index' => false,
    ],

    [
        'name' => 'mariadb-window-functions.sql',
        'type' => 'mysql',
        'content' => <<<'SQL'
-- Window functions (MariaDB 10.2+)
SELECT
    id,
    name,
    salary,
    ROW_NUMBER() OVER (ORDER BY salary DESC) AS rank,
    RANK() OVER (ORDER BY salary DESC) AS salary_rank,
    AVG(salary) OVER () AS avg_salary,
    LAG(salary) OVER (ORDER BY id) AS previous_salary,
    LEAD(salary) OVER (ORDER BY id) AS next_salary
FROM employees;

-- Partitioned window functions
SELECT
    id,
    department,
    salary,
    ROW_NUMBER() OVER (PARTITION BY department ORDER BY salary DESC) AS dept_rank
FROM employees;
SQL,
        'public_content_slug' => 'mariadb-window-functions',
        'public_content_index' => false,
    ],

    [
        'name' => 'mariadb-backup-commands.shell',
        'type' => 'shell',
        'content' => <<<'SHELL'
# Backup single database
mysqldump -u root -p database_name > backup.sql

# Backup all databases
mysqldump -u root -p --all-databases > all_databases.sql

# Backup with compression
mysqldump -u root -p database_name | gzip > backup.sql.gz

# Backup specific tables
mysqldump -u root -p database_name table1 table2 > backup.sql

# Restore from backup
mysql -u root -p database_name < backup.sql

# Restore with progress
pv backup.sql | mysql -u root -p database_name

# Backup with extended insert (faster)
mysqldump -u root -p --extended-insert database_name > backup.sql

# Backup only structure (no data)
mysqldump -u root -p --no-data database_name > structure.sql

# Backup only data (no structure)
mysqldump -u root -p --no-create-info database_name > data.sql
SHELL,
        'public_content_slug' => 'mariadb-backup-commands',
        'public_content_index' => false,
    ],

    // =========================
    // MySQL - Transactions & Locks
    // =========================
    [
        'name' => 'mysql-transactions.sql',
        'type' => 'mysql',
        'content' => <<<'SQL'
-- Start transaction
START TRANSACTION;

-- Execute multiple queries
UPDATE accounts SET balance = balance - 100 WHERE id = 1;
UPDATE accounts SET balance = balance + 100 WHERE id = 2;

-- Commit transaction
COMMIT;

-- Or rollback if error
ROLLBACK;

-- Set isolation level
SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;

-- Or
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;
SQL,
        'public_content_slug' => 'mysql-transactions',
        'public_content_index' => false,
    ],

    [
        'name' => 'mysql-locks.sql',
        'type' => 'mysql',
        'content' => <<<'SQL'
-- View current locks
SHOW OPEN TABLES WHERE In_use > 0;

-- View locks from performance schema
SELECT * FROM performance_schema.metadata_locks;

-- Lock table manually
LOCK TABLES table_name READ;
LOCK TABLES table_name WRITE;

-- Unlock tables
UNLOCK TABLES;

-- Deadlock info
SHOW ENGINE INNODB STATUS;
SQL,
        'public_content_slug' => 'mysql-locks',
        'public_content_index' => false,
    ],
];
