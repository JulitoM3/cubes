<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Reports;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;
use Julitom3\Cubes\Datasets\Dataset;
use Julitom3\Cubes\Datasets\Field;
use Julitom3\Cubes\Enums\Aggregation;

/**
 * The SQL of a field, quoted by the grammar of the connection that runs the query.
 *
 * Tables, columns, and aliases are validated identifiers; a field expression is trusted
 * SQL written by the developer in the dataset, never input received from a report.
 */
final readonly class FieldExpression implements Expression
{
    public function __construct(
        private Dataset $dataset,
        private Field $field,
        private ?Aggregation $aggregation = null,
        private ?string $alias = null,
    ) {}

    public function getValue(Grammar $grammar): string
    {
        $sql = $this->field->expression
            ?? $grammar->wrap("{$this->dataset->table()}.{$this->field->column}");

        if ($this->aggregation !== null) {
            $sql = "{$this->aggregation->sql()}({$sql})";
        }

        if ($this->alias !== null) {
            $sql .= ' as '.$grammar->wrap($this->alias);
        }

        return $sql;
    }
}
