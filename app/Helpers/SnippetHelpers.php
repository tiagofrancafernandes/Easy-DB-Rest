<?php

namespace App\Helpers;

use Illuminate\Support\Fluent;
use App\Enums\SnippetType;
use App\Models\User;
use App\Models\Snippet;

class SnippetHelpers
{
    public static function prepareToInsert(
        null|array|Fluent|Snippet $data = null,
        ?User $user = null,
        ?SnippetType $type = null,
    ): Fluent {
        $data ??= [];

        if (is_object($data)) {
            $data = is_a($data, Fluent::class) ? $data : fluent(is_a($data, Snippet::class) ? $data->toArray() : $data);
        }

        if (!is_object($data)) {
            $data = fluent($data);
        }

        return fluent(
            array_merge(
                [
                    'user_id' => $user ? $user?->id : $data->get('user_id', auth()->user()?->id),
                    'name' => $data->get('name'),
                    'type' => $type ?: SnippetType::tryFromMany(
                        $data->get('type'),
                        SnippetType::MARKDOWN,
                    ),
                    'content' => $data->get('content'),
                    'public_content_slug' => $data->get('public_content_slug'),
                    'public_content_password' => $data->get('public_content_password'),
                    'public_content_index' => $data->get('public_content_index'),
                ],
                $data->toArray()
            )
        );
    }
}
