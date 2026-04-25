<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConnectionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'driver'      => $this->driver,
            'url'         => $this->url,
            'host'        => $this->host,
            'port'        => $this->port,
            'database'    => $this->database,
            'username'    => $this->username,
            'charset'     => $this->charset,
            'collation'   => $this->collation,
            'prefix'      => $this->prefix,
            'search_path' => $this->search_path,
            'sslmode'     => $this->sslmode,
            'schema'      => $this->schema,
            'timeout'     => $this->timeout,
            'options'     => $this->options,
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
                'email' => $this->user?->email,
            ],
            'tags'        => $this->tags ? $this->tags->pluck('name') : [],
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
