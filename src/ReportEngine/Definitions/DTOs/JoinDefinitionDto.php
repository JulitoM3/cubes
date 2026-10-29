<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs;

use Julitom3\Cubes\Datasets\Enums\JoinType;

/**
 * One JOIN clause: "{joinType} join {table} on {from}.{localKey} = {table}.{foreignKey}".
 *
 * "table" is the table being joined and owns the foreign key; "from" is a table already in
 * the query (the root or an earlier join) and owns the local key.
 */
final readonly class JoinDefinitionDto
{
    public function __construct(
        public string $table,
        public string $from,
        public string $localKey,
        public string $foreignKey,
        public JoinType $joinType,
    ) {}
}
