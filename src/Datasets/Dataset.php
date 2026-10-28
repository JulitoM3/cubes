<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Datasets;

use Illuminate\Support\Str;
use Julitom3\Cubes\Exceptions\CubesException;

abstract class Dataset
{
    /** @var array<string, Field>|null */
    private ?array $fieldMap = null;

    /**
     * The database table the dataset reads from.
     */
    abstract public function table(): string;

    /**
     * The fields that reports can use.
     *
     * @return list<Field>
     */
    abstract public function fields(): array;

    /**
     * The relations to other datasets, used to resolve joins automatically.
     *
     * @return list<Relation>
     */
    public function relations(): array
    {
        return [];
    }

    /**
     * The name used to reference the dataset, e.g. "sales" in "sales.amount".
     */
    public function name(): string
    {
        return $this->table();
    }

    public function label(): string
    {
        return Str::headline($this->name());
    }

    public function field(string $column): Field
    {
        return $this->fieldMap()[$column]
            ?? throw CubesException::fieldNotFound($this->name(), $column);
    }

    public function hasField(string $column): bool
    {
        return isset($this->fieldMap()[$column]);
    }

    /**
     * The stable reference of a field, e.g. "sales.amount".
     */
    public function reference(Field $field): string
    {
        return "{$this->name()}.{$field->column}";
    }

    /**
     * @return array<string, Field>
     */
    public function fieldMap(): array
    {
        if ($this->fieldMap !== null) {
            return $this->fieldMap;
        }

        $map = [];

        foreach ($this->fields() as $field) {
            $map[$field->column] = $field;
        }

        return $this->fieldMap = $map;
    }

    /**
     * The fields as the frontend palette shows them.
     *
     * @return list<FieldPaletteDto>
     */
    public function palette(): array
    {
        return array_map(
            fn (Field $field): FieldPaletteDto => FieldPaletteDto::createFromField($this, $field),
            array_values($this->fieldMap()),
        );
    }

    /**
     * Describe the dataset so any frontend can build a field palette.
     *
     * @return array{name: string, label: string, fields: list<array{key: string, dataset: string, label: string, type: string, format: string, pivotable: bool, aggregations: list<string>}>}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name(),
            'label' => $this->label(),
            'fields' => array_map(fn (FieldPaletteDto $field): array => $field->toArray(), $this->palette()),
        ];
    }
}
