<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Enums;

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
     * Guess the format of a column from its database type name.
     */
    public static function fromColumnType(string $type): self
    {
        return match (strtolower($type)) {
            'tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint',
            'int2', 'int4', 'int8', 'serial', 'bigserial', 'smallserial' => self::Integer,
            'decimal', 'numeric', 'float', 'double', 'real',
            'float4', 'float8', 'money', 'smallmoney' => self::Decimal,
            'bool', 'boolean', 'bit' => self::Boolean,
            'date' => self::Date,
            'datetime', 'datetime2', 'timestamp', 'timestamptz', 'smalldatetime', 'datetimeoffset' => self::DateTime,
            'year' => self::Year,
            default => self::Text,
        };
    }
}
