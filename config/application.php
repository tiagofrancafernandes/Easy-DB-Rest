<?php

return [
    'server' => [
        /**
         * List of IPs (IP range) used by the application.
         * Useful for users to release IPs in applications bypass/rules IPs such as Firewalls, EC2 Security Group, etc
         */
        'ip_list' => [
            /**
             * Ex:
             * 200.227.0.0/24
             * 160.0.0.0/12
             * 160.200.0.0/24
             */
        ],
    ],

    'query' => [
        /**
         * Maximum time in seconds a query is allowed to run before being interrupted.
         */
        'default_timeout' => (int) env('QUERY_DEFAULT_TIMEOUT', 30),

        /**
         * Maximum number of rows returned in a single query execution.
         */
        'max_rows' => (int) env('QUERY_MAX_ROWS', 1000),

        /**
         * When true, dangerous statements are blocked regardless of connection config.
         */
        'block_dangerous_statements' => (bool) env('QUERY_BLOCK_DANGEROUS', true),

        /**
         * SQL statement prefixes that are considered dangerous and will be blocked.
         */
        'blocked_statements' => [
            'DROP',
            'TRUNCATE',
            'ALTER',
            'CREATE',
            'RENAME',
            'REPLACE',
        ],
    ],

    'connections' => [
        /**
         * Drivers that are permitted for dynamic connection configurations.
         */
        'allowed_drivers' => [
            'sqlite',
            'pgsql',
            'mysql',
            'sqlsrv',
        ],

        /**
         * Eloquent / Query Builder methods allowed in declarative JSON queries.
         * Any method not in this list will be rejected by the security guard.
         */
        'allowed_builder_methods' => [
            'select',
            'addSelect',
            'where',
            'orWhere',
            'whereIn',
            'whereNotIn',
            'whereNull',
            'whereNotNull',
            'whereBetween',
            'whereDate',
            'whereYear',
            'whereMonth',
            'whereDay',
            'orderBy',
            'orderByDesc',
            'groupBy',
            'having',
            'limit',
            'take',
            'offset',
            'skip',
            'join',
            'leftJoin',
            'rightJoin',
            'distinct',
        ],
    ],
];
