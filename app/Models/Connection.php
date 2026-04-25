<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Tags\HasTags;

class Connection extends Model
{
    use HasFactory;
    use HasUuids;
    use HasTags;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'driver',
        'url',
        'host',
        'port',
        'database',
        'username',
        'password',
        'charset',
        'collation',
        'prefix',
        'search_path',
        'sslmode',
        'schema',
        'timeout',
        'options',
    ];

    /**
     * @var array<string, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'host'    => 'string',
            'port'    => 'integer',
            'timeout' => 'integer',
            'options' => 'array',
            'password' => 'encrypted',
        ];
    }
}
