<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs;

use LogicException;

/**
 * The contract of a report query: how the SQL looks, clause by clause, before it is compiled.
 *
 * Every table, column, and alias here comes from a registered dataset, never from report input.
 * It is also the only source of the select aliases, which the result and the matrix read back.
 */
final readonly class QueryDefinitionDto
{
    /**
     * @param  list<SelectDefinitionDto>  $selects
     * @param  list<JoinDefinitionDto>  $joins
     * @param  list<GroupByDefinitionDto>  $groupBy
     * @param  list<OrderByDefinitionDto>  $orders
     */
    public function __construct(
        public FromDefinitionDto $from,
        public array $selects,
        public array $joins = [],
        public array $groupBy = [],
        public array $orders = [],
    ) {}

    /**
     * The alias the given selected field is selected as.
     */
    public function aliasOf(DimensionDefinitionDto|ValueDefinitionDto $origin): string
    {
        foreach ($this->selects as $select) {
            if ($select->origin === $origin) {
                return $select->alias;
            }
        }

        throw new LogicException('The field is not selected by this query definition.');
    }
}
