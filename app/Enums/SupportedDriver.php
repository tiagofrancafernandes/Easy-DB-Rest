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
            self::Pgsql  => 5432,
            self::Mysql  => 3306,
            self::Sqlsrv => 1433,
            self::Sqlite => null,
        };
    }
}
