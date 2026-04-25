<?php

declare(strict_types=1);

namespace App\Enums;

enum SnippetType: string
{
    case SQL = 'sql';
    case PGSQL = 'pgsql';
    case MYSQL = 'mysql';
    case JAVASCRIPT = 'javascript';
    case TYPESCRIPT = 'typescript';
    case PHP = 'php';
    case MARKDOWN = 'markdown';
    case JSON = 'json';
    case JSON_COMMENTS = 'json-with-comments';
    case HTTP = 'http';
    case SHELL = 'shell';
    case CSV = 'csv';
    case TEXT = 'text';
    case UNKNOWN = 'unknown';

    /**
     * Summary of tryFromFileName
     *
     * @param null|string $value
     * @param null|self $default
     *
     * @return ?static
     */
    public static function tryFromFileName(?string $value, null|self $default = null): ?static
    {
        $value = strtolower(trim($value ?? ''));
        $value = trim(explode(PHP_EOL, $value)[0] ?? '');

        if (!$value) {
            return $default;
        }

        $parts = explode('.', $value);

        if (count($parts) < 2) {
            return $default;
        }

        // Try exact match for multiple extensions (e.g., blade.php, pg.sql)
        $lastTwo = implode('.', array_slice($parts, -2));
        $lastOne = end($parts);

        $cases = collect(static::cases());

        foreach ([$lastTwo, $lastOne] as $ext) {
            $found = $cases->first(fn ($e) => in_array(
                $ext,
                array_map('strtolower', $e->getExtensions())
            ));

            if ($found) {
                return $found;
            }
        }

        return $default;
    }

    /**
     * Summary of tryFromMany
     *
     * @param mixed $value
     * @param null|self $default
     *
     * @return ?static
     */
    public static function tryFromMany(mixed $value, null|self $default = null): ?static
    {
        if (!$value) {
            return $default ?? static::UNKNOWN;
        }

        if ($value instanceof self) {
            return $value;
        }

        if (is_string($value) && trim($value)) {
            $normalized = strtolower(trim($value));

            $exactMatch = collect(static::cases())->first(fn ($e) => $e->value === $normalized);

            if ($exactMatch) {
                return $exactMatch;
            }

            // Fallback to filename detection
            return static::tryFromFileName((string) $value, default: $default ?? static::UNKNOWN);
        }

        return $default ?? static::UNKNOWN;
    }

    /**
     * Summary of getExtensions
     *
     * @return string[]|array
     */
    public function getExtensions(): array
    {
        return match ($this) {
            static::SQL => ['sql'],
            static::PGSQL => ['pg.sql', 'pgsql'],
            static::MYSQL => ['my.sql', 'mysql'],
            static::JAVASCRIPT => ['js', 'cjs', 'mjs'],
            static::TYPESCRIPT => ['ts', 'tsx'],
            static::PHP => ['php', 'blade.php'],
            static::MARKDOWN => ['md', 'markdown'],
            static::JSON => ['json'],
            static::JSON_COMMENTS => ['jsonc'],
            static::HTTP => ['http'],
            static::SHELL => ['sh', 'bash', 'zsh'],
            static::CSV => ['csv'],
            static::TEXT => ['txt', 'text', 'plain'],
            static::UNKNOWN => [],
            default => ['txt'],
        };
    }

    /**
     * Summary of getExtension
     *
     * @return string
     */
    public function getExtension(): string
    {
        return $this->getExtensions()[0] ?? 'txt';
    }
}
