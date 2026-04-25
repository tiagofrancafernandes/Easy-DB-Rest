<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\SupportedDriver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100'],
            'driver'      => ['required', 'string', Rule::enum(SupportedDriver::class)],
            'url'         => ['nullable', 'string', 'max:2000'],
            'host'        => ['nullable', 'string', 'max:255'],
            'port'        => ['nullable', 'integer', 'min:1', 'max:65535'],
            'database'    => ['required_without:url', 'string', 'max:255'],
            'username'    => ['nullable', 'string', 'max:100'],
            'password'    => ['nullable', 'string'],
            'charset'     => ['nullable', 'string', 'max:50'],
            'collation'   => ['nullable', 'string', 'max:50'],
            'prefix'      => ['nullable', 'string', 'max:50'],
            'search_path' => ['nullable', 'string', 'max:100'],
            'sslmode'     => ['nullable', 'string', 'max:50'],
            'schema'      => ['nullable', 'string', 'max:100'],
            'timeout'     => ['nullable', 'integer', 'min:1', 'max:300'],
            'options'     => ['nullable', 'array'],
            'tags'        => ['nullable', 'array'],
            'tags.*'      => ['string', 'max:50'],
        ];
    }
}
