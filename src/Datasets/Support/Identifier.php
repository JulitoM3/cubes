<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Datasets\Support;

final class Identifier
{
    /**
     * Determine whether the value is a safe SQL identifier (table or column name).
     */
    public static function isValid(string $value): bool
    {
        return preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $value) === 1;
    }
}
