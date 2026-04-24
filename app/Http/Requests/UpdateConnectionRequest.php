<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\SupportedDriver;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConnectionRequest extends FormRequest
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
            'name'     => ['sometimes', 'string', 'max:100'],
            'driver'   => ['sometimes', 'string', Rule::enum(SupportedDriver::class)],
            'host'     => ['sometimes', 'nullable', 'string', 'max:255'],
            'port'     => ['sometimes', 'nullable', 'integer', 'min:1', 'max:65535'],
            'database' => ['sometimes', 'string', 'max:255'],
            'username' => ['sometimes', 'nullable', 'string', 'max:100'],
            'password' => ['sometimes', 'nullable', 'string'],
            'schema'   => ['sometimes', 'nullable', 'string', 'max:100'],
            'timeout'  => ['sometimes', 'nullable', 'integer', 'min:1', 'max:300'],
            'options'  => ['sometimes', 'nullable', 'array'],
        ];
    }
}
