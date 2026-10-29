<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Compilers;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;
use Julitom3\Cubes\Datasets\Enums\Aggregation;

/**
 * The SQL of a column, quoted by the grammar of the connection that runs the query.
 *
 * Tables, columns, and aliases are validated identifiers; an expression is trusted SQL
 * written by the developer in a dataset, never input received from a report.
 */
final readonly class SqlExpression implements Expression
{
    public function __construct(
        private string $table,
        private string $column,
        private ?string $expression = null,
        private ?Aggregation $aggregation = null,
        private ?string $alias = null,
    ) {}

    public function getValue(Grammar $grammar): string
    {
        $sql = $this->expression ?? $grammar->wrap("{$this->table}.{$this->column}");

        if ($this->aggregation !== null) {
            $sql = "{$this->aggregation->sql()}({$sql})";
        }

        if ($this->alias !== null) {
            $sql .= ' as '.$grammar->wrap($this->alias);
        }

        return $sql;
    }
}
