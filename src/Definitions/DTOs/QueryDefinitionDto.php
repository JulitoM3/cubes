<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Definitions\DTOs;

/**
 * The contract of a report query: how the SQL looks, clause by clause, before it is compiled.
 *
 * Every table, column, and alias here comes from a registered dataset, never from report input.
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
}
