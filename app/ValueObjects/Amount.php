<?php

namespace App\ValueObjects;

use Stringable;
use Illuminate\Http\Request;

class Amount implements Stringable
{
    private int $amountInCents;

    public function __construct(null|string|float|int $value = null, ?Request $request = null)
    {
        $value = static::prepareAmountValueAsFloat($value);
        $value ??= static::valueFromRequest($request);

        $this->amountInCents = (int) round($value * 100);
    }

    public static function init(null|string|float|int $value = null, ?Request $request = null): static
    {
        return new static($value, $request);
    }

    public static function prepareAmountValueAsFloat(string|int|float|null $amount = null): float|null
    {
        if (is_null($amount) || !is_numeric($amount) || $amount < 0) {
            return null;
        }

        $floatValue = round(filter_var(round($amount, 4), FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) ?? 0, 4);
        $normalized = number_format($floatValue, 4, '.', '');

        return round($normalized, 2);
    }

    public static function prepareAmountValueAsInt(string|int|float|null $amount = null): int|null
    {
        $amount = static::prepareAmountValueAsFloat($amount);
        $normalized = number_format($amount ?? 0, 4, '.', '');

        return round($normalized, 3) * 100;
    }

    public static function getFromCents(string|int|null $amount = null): float|null
    {
        $amount = static::prepareAmountValueAsFloat($amount);

        if (is_null($amount)) {
            return null;
        }

        return $amount === 0 ? 0 : $amount / 100;
    }

    public static function getToCents(string|int|float|null $amount = null): int|null
    {
        $amount = static::prepareAmountValueAsFloat($amount);

        if (is_null($amount)) {
            return null;
        }

        return $amount * 100;
    }

    public static function valueFromRequest(?Request $request = null): string|float|int|null
    {
        $request ??= request();
        $amount = $request->input('amount');

        return round(round(filter_var($amount, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE) ?? 0, 4), 2);
    }

    public static function fromRequest(?Request $request = null): static
    {
        $amount = static::valueFromRequest($request);

        return new static($amount);
    }

    public static function fromCents(int $cents): static
    {
        $instance = new static(0);
        $instance->amountInCents = $cents;

        return $instance;
    }

    public function toInt(): int
    {
        return static::getToCents($this->amountInCents);
    }

    public function toFloat(): float
    {
        return static::getFromCents($this->amountInCents);
    }

    public function __toString(): string
    {
        return (string) $this->toFloat();
    }
}
