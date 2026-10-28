<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Datasets;

use Julitom3\Cubes\Enums\JoinType;
use Julitom3\Cubes\Exceptions\CubesException;

final readonly class Relation
{
    /**
     * @param  string  $table  The related table, which must belong to a registered dataset.
     * @param  string  $localKey  The column on this dataset's table.
     * @param  string  $foreignKey  The column on the related table.
     */
    public function __construct(
        public string $table,
        public string $localKey,
        public string $foreignKey = 'id',
        public JoinType $type = JoinType::Left,
    ) {
        foreach ([$table, $localKey, $foreignKey] as $identifier) {
            if (! Identifier::isValid($identifier)) {
                throw CubesException::invalidIdentifier($identifier);
            }
        }
    }
}
