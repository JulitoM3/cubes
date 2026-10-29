<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Datasets\Enums;

enum Aggregation: string
{
    case Count = 'count';
    case Sum = 'sum';
    case Avg = 'avg';
    case Min = 'min';
    case Max = 'max';

    /**
     * Resolve an aggregation from its name, ignoring case.
     */
    public static function fromName(string $name): ?self
    {
        return self::tryFrom(strtolower(trim($name)));
    }

    /**
     * The SQL function used to compile the aggregation.
     */
    public function sql(): string
    {
        return strtoupper($this->value);
    }
}
