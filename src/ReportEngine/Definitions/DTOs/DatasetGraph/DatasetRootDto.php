<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs\DatasetGraph;

/**
 * The node the query starts from and the nodes it has to reach.
 */
final readonly class DatasetRootDto
{
    /**
     * @param  list<DatasetNodeDto>  $destinations  Every selected node except the root, in selection order.
     */
    public function __construct(
        public DatasetNodeDto $root,
        public array $destinations = [],
    ) {}
}
