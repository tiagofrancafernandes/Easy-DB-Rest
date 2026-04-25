<?php

declare(strict_types=1);

namespace App\Enums;

enum SupportedDriver: string
{
    case Sqlite = 'sqlite';
    case Pgsql  = 'pgsql';
    case Mysql  = 'mysql';
    case Sqlsrv = 'sqlsrv';

    public function defaultPort(): ?int
    {
        return match ($this) {
            static::Pgsql  => 5432,
            static::Mysql  => 3306,
            static::Sqlsrv => 1433,
            static::Sqlite => null,
        };
    }
}
