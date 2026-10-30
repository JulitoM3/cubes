<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Datasets;

use Julitom3\Cubes\Datasets\DTOs\Dimension;
use Julitom3\Cubes\Datasets\DTOs\FieldDefinitionDto;
use Julitom3\Cubes\Datasets\DTOs\Metric;
use Julitom3\Cubes\Datasets\DTOs\Relation;

/**
 * What a Cubable model exposes to reports, built fluently in its cubeSchema() method.
 *
 * Fields are Dimension and Metric objects; a field without a format takes it from the model casts.
 */
final class CubeSchema
{
    private ?string $category = null;

    private ?string $label = null;

    /** @var list<FieldDefinitionDto> */
    private array $fields = [];

    /** @var array<string, Relation> */
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
     * The fields that rows and columns can be grouped by. Variadic so PHP rejects anything but a Dimension.
     */
    public function dimensions(Dimension ...$dimensions): self
    {
        $this->fields = [...$this->fields, ...array_values($dimensions)];

        return $this;
    }

    /**
     * The numeric fields that values are aggregated from. Variadic so PHP rejects anything but a Metric.
     */
    public function metrics(Metric ...$metrics): self
    {
        $this->fields = [...$this->fields, ...array_values($metrics)];

        return $this;
    }

    /**
     * The relation methods of the model that connect it with other Cubable models.
     * Variadic so PHP rejects anything but a Relation.
     */
    public function relations(Relation ...$relations): self
    {
        foreach ($relations as $relation) {
            $this->relations[$relation->name] = $relation;
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
     * @return list<FieldDefinitionDto>
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return list<Relation>
     */
    public function getRelations(): array
    {
        return array_values($this->relations);
    }
}
