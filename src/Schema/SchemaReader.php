<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Schema;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Schema\Builder;
use Julitom3\Cubes\Datasets\Identifier;
use Julitom3\Cubes\Datasets\Relation;

final readonly class SchemaReader
{
    public function __construct(
        private DatabaseManager $database,
        private Repository $config,
    ) {}

    public function hasTable(string $table): bool
    {
        return $this->schema()->hasTable($table);
    }

    /**
     * The columns of the table that should become fields, without the excluded ones.
     *
     * @return list<array{name: string, type_name: string}>
     */
    public function columns(string $table): array
    {
        $excluded = $this->config->get('cubes.generator.exclude_columns', []);
        $excluded = is_array($excluded) ? $excluded : [];

        $columns = [];

        foreach ($this->schema()->getColumns($table) as $column) {
            if (in_array($column['name'], $excluded, true) || ! Identifier::isValid($column['name'])) {
                continue;
            }

            $columns[] = ['name' => $column['name'], 'type_name' => $column['type_name']];
        }

        return $columns;
    }

    /**
     * The single-column foreign keys of the table, as dataset relations.
     *
     * @return list<Relation>
     */
    public function relations(string $table): array
    {
        $relations = [];

        foreach ($this->schema()->getForeignKeys($table) as $foreignKey) {
            if (count($foreignKey['columns']) !== 1 || count($foreignKey['foreign_columns']) !== 1) {
                continue;
            }

            $relations[] = new Relation(
                table: $foreignKey['foreign_table'],
                localKey: $foreignKey['columns'][0],
                foreignKey: $foreignKey['foreign_columns'][0],
            );
        }

        return $relations;
    }

    private function schema(): Builder
    {
        $connection = $this->config->get('cubes.connection');

        return $this->database
            ->connection(is_string($connection) ? $connection : null)
            ->getSchemaBuilder();
    }
}
