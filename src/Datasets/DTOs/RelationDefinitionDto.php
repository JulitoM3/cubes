<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Datasets\DTOs;

use Julitom3\Cubes\Datasets\Enums\JoinType;
use Julitom3\Cubes\Datasets\Support\Identifier;
use Julitom3\Cubes\Exceptions\CubesException;

/**
 * A relation declared by a dataset: "{dataset table}.{localKey} = {table}.{foreignKey}".
 */
final readonly class RelationDefinitionDto
{
    /**
     * @param  string  $table  The related table, which must belong to a registered dataset.
     * @param  string  $localKey  The column on the table of the dataset that declares the relation.
     * @param  string  $foreignKey  The column on the related table.
     */
    public function __construct(
        public string $table,
        public string $localKey,
        public string $foreignKey = 'id',
        public JoinType $joinType = JoinType::Left,
    ) {
        foreach ([$table, $localKey, $foreignKey] as $identifier) {
            if (! Identifier::isValid($identifier)) {
                throw CubesException::invalidIdentifier($identifier);
            }
        }
    }
}
