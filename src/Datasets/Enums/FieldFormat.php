<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Datasets\Enums;

enum FieldFormat: string
{
    case Text = 'text';
    case Integer = 'integer';
    case Decimal = 'decimal';
    case Currency = 'currency';
    case Percentage = 'percentage';
    case Boolean = 'boolean';
    case Date = 'date';
    case DateTime = 'date_time';
    case Year = 'year';

    /**
     * Guess the format of a column from its Eloquent cast, e.g. "decimal:2" or "datetime".
     * Returns null for casts without a matching format, such as strings or custom casts.
     */
    public static function fromCast(?string $cast): ?self
    {
        if ($cast === null) {
            return null;
        }

        return match (strtolower(explode(':', $cast, 2)[0])) {
            'int', 'integer' => self::Integer,
            'real', 'float', 'double', 'decimal' => self::Decimal,
            'bool', 'boolean' => self::Boolean,
            'date', 'immutable_date' => self::Date,
            'datetime', 'immutable_datetime', 'timestamp' => self::DateTime,
            default => null,
        };
    }
}
