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
            'id'         => $this->id,
            'name'       => $this->name,
            'driver'     => $this->driver,
            'host'       => $this->host,
            'port'       => $this->port,
            'database'   => $this->database,
            'username'   => $this->username,
            'schema'     => $this->schema,
            'timeout'    => $this->timeout,
            'options'    => $this->options,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
