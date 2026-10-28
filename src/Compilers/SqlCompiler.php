<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Compilers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\Builder;
use Julitom3\Cubes\Compilers\Contracts\ClauseCompiler;
use Julitom3\Cubes\Definitions\DTOs\QueryDefinitionDto;

/**
 * Compile a query definition into a query builder, one clause compiler at a time.
 */
final readonly class SqlCompiler
{
    /** @var list<ClauseCompiler> */
    private array $compilers;

    public function __construct(
        private DatabaseManager $database,
        private Repository $config,
        FromCompiler $from,
        JoinCompiler $join,
        SelectCompiler $select,
        GroupByCompiler $groupBy,
        OrderByCompiler $orderBy,
    ) {
        $this->compilers = [$from, $join, $select, $groupBy, $orderBy];
    }

    public function compile(QueryDefinitionDto $query): Builder
    {
        $connection = $this->config->get('cubes.connection');
        $builder = $this->database->connection(is_string($connection) ? $connection : null)->query();

        foreach ($this->compilers as $compiler) {
            $compiler->compile($builder, $query);
        }

        return $builder;
    }
}
