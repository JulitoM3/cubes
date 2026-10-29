<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Datasets;

use Julitom3\Cubes\Datasets\DTOs\FieldDefinitionDto;
use Julitom3\Cubes\Datasets\Enums\FieldType;
use Julitom3\Cubes\Datasets\Enums\JoinType;

/**
 * What a Cubable model exposes to reports, built fluently in its cubeSchema() method.
 *
 * A field is a column name, which takes its label from the column and its format from the
 * model casts, or a FieldDefinitionDto to set the label, format, aggregations, or expression.
 */
final class CubeSchema
{
    private ?string $category = null;

    private ?string $label = null;

    /** @var list<array{FieldType, string|FieldDefinitionDto}> */
    private array $fields = [];

    /** @var array<string, JoinType> */
    private array $relations = [];

    public static function make(): self
    {
        return new self;
    }

    /**
     * The name used to reference the dataset, e.g. "sales" in "sales.amount". Defaults to the table.
     */
    public function category(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * The name users see for the dataset in Cubes::catalog(). Defaults to the category as a headline.
     */
    public function label(string $label): self
    {
        $this->label = $label;

        return $this;
    }

    /**
     * The fields that rows and columns can be grouped by.
     *
     * @param  list<string|FieldDefinitionDto>  $fields
     */
    public function dimensions(array $fields): self
    {
        foreach ($fields as $field) {
            $this->fields[] = [FieldType::Dimension, $field];
        }

        return $this;
    }

    /**
     * The numeric fields that values are aggregated from.
     *
     * @param  list<string|FieldDefinitionDto>  $fields
     */
    public function metrics(array $fields): self
    {
        foreach ($fields as $field) {
            $this->fields[] = [FieldType::Metric, $field];
        }

        return $this;
    }

    /**
     * The relation methods of the model that connect it with other Cubable models,
     * e.g. ["store", "product" => JoinType::Inner]. Joins are left joins unless given.
     *
     * @param  array<int|string, string|JoinType>  $relations
     */
    public function relations(array $relations): self
    {
        foreach ($relations as $name => $joinType) {
            if (is_string($joinType)) {
                $this->relations[$joinType] = JoinType::Left;
            } elseif (is_string($name)) {
                $this->relations[$name] = $joinType;
            }
        }

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * @return list<array{FieldType, string|FieldDefinitionDto}>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array<string, JoinType>
     */
    public function getRelations(): array
    {
        return $this->relations;
    }
}
