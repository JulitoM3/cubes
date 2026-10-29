<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Datasets\Contracts;

use Illuminate\Support\Str;
use Julitom3\Cubes\Datasets\Collections\FieldCollection;
use Julitom3\Cubes\Datasets\Collections\RelationCollection;
use Julitom3\Cubes\Datasets\DTOs\FieldDefinitionDto;
use Julitom3\Cubes\Exceptions\CubesException;

abstract class DatasetContract
{
    /** @var array<string, FieldDefinitionDto>|null */
    private ?array $fieldMap = null;

    /**
     * The database table the dataset reads from.
     */
    abstract public function table(): string;

    /**
     * The fields that reports can use.
     */
    abstract public function fields(): FieldCollection;

    /**
     * The relations to other datasets, used to build the dataset graph.
     */
    public function relations(): RelationCollection
    {
        return new RelationCollection;
    }

    /**
     * The name used to reference the dataset, e.g. "sales" in "sales.amount".
     */
    public function category(): string
    {
        return $this->table();
    }

    public function label(): string
    {
        return Str::headline($this->category());
    }

    public function getField(string $column): FieldDefinitionDto
    {
        return $this->fieldMap()[$column]
            ?? throw CubesException::fieldNotFound($this->category(), $column);
    }

    public function hasField(string $column): bool
    {
        return isset($this->fieldMap()[$column]);
    }

    /**
     * The stable reference of a field, e.g. "sales.amount".
     */
    public function reference(FieldDefinitionDto $field): string
    {
        return "{$this->category()}.{$field->column}";
    }

    /**
     * @return array<string, FieldDefinitionDto>
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
}
