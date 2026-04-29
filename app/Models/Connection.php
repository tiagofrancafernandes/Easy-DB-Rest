<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use PDOException;
use Closure;
use App\Services\QueryExecutorService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Tags\HasTags;

/**
 * @property string $id
 * @property string $name
 * @property string $driver
 * @property string|null $host
 * @property int|null $port
 * @property string|null $database
 * @property string|null $username
 * @property string|null $password
 * @property string|null $schema
 * @property int|null $timeout
 * @property array<array-key, mixed>|null $options
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $url
 * @property string|null $charset
 * @property string|null $collation
 * @property string|null $prefix
 * @property string|null $search_path
 * @property string|null $sslmode
 * @property \Illuminate\Database\Eloquent\Collection<int, \Spatie\Tags\Tag> $tags
 * @property-read int|null $tags_count
 * @method static \Database\Factories\ConnectionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereCharset($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereCollation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereDatabase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection wherePort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection wherePrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereSchema($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereSearchPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereSslmode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereTimeout($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection withAllTags(\ArrayAccess|\Spatie\Tags\Tag|array|string $tags, ?string $type = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection withAllTagsOfAnyType($tags)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection withAnyTags(\ArrayAccess|\Spatie\Tags\Tag|array|string $tags, ?string $type = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection withAnyTagsOfAnyType($tags)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection withAnyTagsOfType(array|string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection withoutTags(\ArrayAccess|\Spatie\Tags\Tag|array|string $tags, ?string $type = null)
 * @property int|null $user_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Team> $teams
 * @property-read int|null $teams_count
 * @property-read User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Connection whereUserId($value)
 * @mixin \Eloquent
 */
class Connection extends Model
{
    use HasFactory;
    use HasUuids;
    use HasTags;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
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
            'host' => 'string',
            'port' => 'integer',
            'timeout' => 'integer',
            'options' => 'array',
            'password' => 'encrypted',
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, $this>
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Team, $this>
     */
    public function teams(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'connection_team')
            ->withPivot('permission')
            ->withTimestamps();
    }

    /**
     * @param array $overrides
     * @param ?QueryExecutorService $executor
     *
     * @return bool
     */
    public function safeTest(
        array $overrides = [],
        ?QueryExecutorService $executor = null,
    ): bool {
        try {
            return $this->test(overrides: $overrides, executor: $executor, catch: fn (mixed $v) => false);
        } catch (\Throwable $th) {
            return false;
        }
    }

    /**
     * @param array $overrides
     * @param ?QueryExecutorService $executor
     * @param Closure|bool|null $catch
     *
     * @return bool
     *
     * @throws PDOException
     */
    public function test(
        array $overrides = [],
        ?QueryExecutorService $executor = null,
        Closure|bool|null $catch = null,
    ): bool {
        try {
            $executor ??= app(QueryExecutorService::class);

            if (isset($overrides['password'])) {
                $overrides['password'] = is_string($overrides['password']) ? $overrides['password'] : null;
            }

            $executor->testConnection(
                configId: $this->id,
                inlineConnection: null,
                overrides: $overrides,
            );

            return true;
        } catch (\Throwable $th) {
            if ($catch) {
                try {
                    $catch = is_a($catch, Closure::class) ? $catch : fn (mixed $v) => false;
                    $catch($th);
                } catch (\Throwable) {
                }

                return false;
            }

            if (!app()->environment(['prod', 'production']) && config('app.debug', false)) {
                throw $th;
            }

            return false;
        }
    }
}
