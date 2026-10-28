<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Exceptions;

use InvalidArgumentException;

final class CubesException extends InvalidArgumentException
{
    public static function invalidDataset(string $class): self
    {
        return self::make('invalid_dataset', ['class' => $class]);
    }

    public static function datasetNotFound(string $dataset): self
    {
        return self::make('dataset_not_found', ['dataset' => $dataset]);
    }

    public static function fieldNotFound(string $dataset, string $field): self
    {
        return self::make('field_not_found', ['dataset' => $dataset, 'field' => $field]);
    }

    public static function invalidFieldReference(string $reference): self
    {
        return self::make('invalid_field_reference', ['reference' => $reference]);
    }

    public static function invalidIdentifier(string $identifier): self
    {
        return self::make('invalid_identifier', ['identifier' => $identifier]);
    }

    public static function invalidSelection(): self
    {
        return self::make('invalid_selection');
    }

    public static function notADimension(string $field): self
    {
        return self::make('not_a_dimension', ['field' => $field]);
    }

    public static function invalidAggregation(string $field, string $aggregation): self
    {
        return self::make('invalid_aggregation', ['field' => $field, 'aggregation' => $aggregation]);
    }

    public static function noPath(string $from, string $to): self
    {
        return self::make('no_path', ['from' => $from, 'to' => $to]);
    }

    public static function emptyReport(): self
    {
        return self::make('empty_report');
    }

    /**
     * @param  array<string, string>  $replace
     */
    private static function make(string $key, array $replace = []): self
    {
        return new self((string) trans("cubes::messages.exceptions.{$key}", $replace));
    }
}
