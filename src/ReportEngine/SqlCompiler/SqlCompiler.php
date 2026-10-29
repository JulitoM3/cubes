<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\SqlCompiler;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Query\Builder;
use Julitom3\Cubes\ReportEngine\Compilers\Contracts\CompilerContract;
use Julitom3\Cubes\ReportEngine\Compilers\FromCompiler;
use Julitom3\Cubes\ReportEngine\Compilers\GroupByCompiler;
use Julitom3\Cubes\ReportEngine\Compilers\JoinCompiler;
use Julitom3\Cubes\ReportEngine\Compilers\OrderByCompiler;
use Julitom3\Cubes\ReportEngine\Compilers\SelectCompiler;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\QueryDefinitionDto;

/**
 * Compile a query definition into a query builder, one clause compiler at a time.
 *
 * It never resolves datasets or relations: everything it needs is in the query definition.
 */
final readonly class SqlCompiler
{
    /** @var list<CompilerContract> */
    private array $compilers;

    public function __construct(
        private DatabaseManager $database,
        private Repository $config,
        FromCompiler $fromCompiler,
        JoinCompiler $joinCompiler,
        SelectCompiler $selectCompiler,
        GroupByCompiler $groupByCompiler,
        OrderByCompiler $orderByCompiler,
    ) {
        $this->compilers = [$fromCompiler, $joinCompiler, $selectCompiler, $groupByCompiler, $orderByCompiler];
    }

    public function compile(QueryDefinitionDto $queryDefinition): Builder
    {
        $connection = $this->config->get('cubes.connection');
        $builder = $this->database->connection(is_string($connection) ? $connection : null)->query();

        foreach ($this->compilers as $compiler) {
            $compiler->compile($builder, $queryDefinition);
        }

        return $builder;
    }
}
