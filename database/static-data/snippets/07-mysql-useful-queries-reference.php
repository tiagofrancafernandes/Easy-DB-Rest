<?php

declare(strict_types=1);

return [
    [
        'name' => 'mysql-complete-reference-guide',
        'type' => 'markdown',
        'content' => <<<'MD'
# MySQL & MariaDB Complete Reference Guide

Using **SQL only** for MySQL and MariaDB. Everything works in common clients (Easy DB Rest, DBeaver, TablePlus, etc.).

---

## 🔎 List Databases

```sql
SHOW DATABASES;
```

Or via catalog:

```sql
SELECT schema_name
FROM information_schema.schemata;
```

---

## 📦 List Tables in a Database

```sql
SHOW TABLES;
```

Or with more details:

```sql
SELECT table_name, table_type
FROM information_schema.tables
WHERE table_schema = 'your_database';
```

---

## 🧬 Table Details

Quick view:

```sql
DESCRIBE your_table;
```

Or complete version:

```sql
SELECT
    column_name,
    column_type,
    is_nullable,
    column_default,
    column_key,
    extra
FROM information_schema.columns
WHERE table_schema = 'your_database'
  AND table_name = 'your_table';
```

---

## 🧬 Details of Multiple Tables at Once

```sql
SELECT
    table_name,
    column_name,
    column_type,
    is_nullable
FROM information_schema.columns
WHERE table_schema = 'your_database'
ORDER BY table_name, ordinal_position;
```

---

## 📊 Table Sizes

```sql
SELECT
    table_name,
    ROUND((data_length + index_length) / 1024 / 1024, 2) AS size_mb
FROM information_schema.tables
WHERE table_schema = 'your_database'
ORDER BY size_mb DESC;
```

---

## 📊 Database Sizes

```sql
SELECT
    table_schema AS database_name,
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
FROM information_schema.tables
GROUP BY table_schema
ORDER BY size_mb DESC;
```

---

## 🔐 View Users and Hosts

```sql
SELECT user, host
FROM mysql.user;
```

---

## ⚡ Active Connections

```sql
SHOW PROCESSLIST;
```

Or more filterable:

```sql
SELECT *
FROM information_schema.processlist
WHERE command != 'Sleep';
```

---

## 🧱 Indexes of a Table

```sql
SHOW INDEX FROM your_table;
```

---

## 🔗 Foreign Keys

```sql
SELECT
    table_name,
    column_name,
    referenced_table_name,
    referenced_column_name
FROM information_schema.key_column_usage
WHERE table_schema = 'your_database'
  AND referenced_table_name IS NOT NULL;
```

---

## 🧠 Server Version

```sql
SELECT VERSION();
```

---

## 🔧 Server Variables

View all:

```sql
SHOW VARIABLES;
```

Or filter by name:

```sql
SHOW VARIABLES LIKE 'max_connections';
```

---

## 📈 General Status

```sql
SHOW STATUS;
```

---

## 🎯 Useful Extras

### Tables with Most Rows (estimated)

```sql
SELECT table_name, table_rows
FROM information_schema.tables
WHERE table_schema = 'your_database'
ORDER BY table_rows DESC;
```

### Storage Engines Used

```sql
SELECT table_name, engine
FROM information_schema.tables
WHERE table_schema = 'your_database';
```

---

## 🧭 Mental Map

* `SHOW ...` → quick and direct
* `information_schema` → powerful and filterable
* `mysql.*` → internal data (users, permissions)

---

## Key Differences from PostgreSQL

If in PostgreSQL you query `pg_catalog`, here the magical equivalent is **information_schema**, which works as a live map of your database structure.
MD,
        'public_content_slug' => 'mysql-complete-reference-guide',
        'public_content_index' => false,
    ],
];
