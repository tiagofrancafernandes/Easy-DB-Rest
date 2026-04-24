<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\QueryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QueryRequest extends FormRequest
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
            'type'              => ['required', 'string', Rule::enum(QueryType::class)],
            'query'             => ['required'],
            'bindings'          => ['nullable', 'array'],
            'table'             => ['nullable', 'string', 'max:100'],
            'execute'           => ['nullable', 'array'],
            'connection'        => ['nullable', 'array'],
            'connection.driver' => ['required_with:connection', 'string'],
            'connection.database' => ['required_with:connection', 'string'],
            'overrides'         => ['nullable', 'array'],
        ];
    }
}
