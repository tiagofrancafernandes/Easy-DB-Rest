<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\SnippetType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Tags\HasTags;

/**
 * @property string $id
 * @property int $user_id
 * @property string $name
 * @property SnippetType $type
 * @property string $content
 * @property string|null $public_content_slug
 * @property string|null $public_content_password
 * @property bool $public_content_index
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Team> $teams
 * @property \Illuminate\Database\Eloquent\Collection<int, \Spatie\Tags\Tag> $tags
 * @property-read int|null $tags_count
 * @property-read int|null $teams_count
 * @method static \Database\Factories\SnippetFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Snippet newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Snippet newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Snippet query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Snippet whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Snippet whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Snippet whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Snippet whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Snippet wherePublicContentIndex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Snippet wherePublicContentPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Snippet wherePublicContentSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Snippet whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Snippet whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Snippet whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Snippet withAllTags(\ArrayAccess|\Spatie\Tags\Tag|array|string $tags, ?string $type = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Snippet withAllTagsOfAnyType($tags)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Snippet withAnyTags(\ArrayAccess|\Spatie\Tags\Tag|array|string $tags, ?string $type = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Snippet withAnyTagsOfAnyType($tags)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Snippet withAnyTagsOfType(array|string $type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Snippet withoutTags(\ArrayAccess|\Spatie\Tags\Tag|array|string $tags, ?string $type = null)
 * @mixin \Eloquent
 */
class Snippet extends Model
{
    /** @use HasFactory<\Database\Factories\SnippetFactory> */
    use HasFactory;
    use HasUuids;
    use HasTags;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'type',
        'content',
        'public_content_slug',
        'public_content_password',
        'public_content_index',
    ];

    /**
     * @var array<string, string>
     */
    protected $hidden = [
        'public_content_password',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => SnippetType::class,
            'public_content_index' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Team, $this>
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'snippet_team')
            ->withPivot('permission')
            ->withTimestamps();
    }
}
