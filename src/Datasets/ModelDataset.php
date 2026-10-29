<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Datasets;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Support\Str;
use Julitom3\Cubes\Datasets\Collections\FieldCollection;
use Julitom3\Cubes\Datasets\Collections\RelationCollection;
use Julitom3\Cubes\Datasets\Contracts\DatasetContract;
use Julitom3\Cubes\Datasets\DTOs\FieldDefinitionDto;
use Julitom3\Cubes\Datasets\DTOs\RelationDefinitionDto;
use Julitom3\Cubes\Datasets\Enums\FieldFormat;
use Julitom3\Cubes\Datasets\Enums\FieldType;
use Julitom3\Cubes\Datasets\Enums\JoinType;
use Julitom3\Cubes\Exceptions\CubesException;
use Julitom3\Cubes\Traits\Cubable;

/**
 * The dataset of a Cubable model, built from its cubeSchema() at runtime.
 *
 * The model is the only source: its table, casts, and relations give the dataset its table,
 * field formats, and join keys, so nothing has to be kept in sync by hand.
 */
final class ModelDataset extends DatasetContract
{
    private CubeSchema $cubeSchema;

    /**
     * @param  Model  $model  A model that uses the Cubable trait.
     */
    public function __construct(private readonly Model $model)
    {
        if (! in_array(Cubable::class, class_uses_recursive($model), true) || ! method_exists($model, 'cubeSchema')) {
            throw CubesException::notCubable($model::class);
        }

        $cubeSchema = $model->cubeSchema();

        if (! $cubeSchema instanceof CubeSchema) {
            throw CubesException::notCubable($model::class);
        }

        $this->cubeSchema = $cubeSchema;
    }

    public function model(): Model
    {
        return $this->model;
    }

    public function table(): string
    {
        return $this->model->getTable();
    }

    public function category(): string
    {
        return $this->cubeSchema->getCategory() ?? $this->table();
    }

    public function label(): string
    {
        return $this->cubeSchema->getLabel() ?? Str::headline($this->category());
    }

    public function fields(): FieldCollection
    {
        return new FieldCollection(array_map(
            fn (array $field): FieldDefinitionDto => $field[1] instanceof FieldDefinitionDto
                ? $field[1]
                : $this->fieldFromColumn($field[0], $field[1]),
            $this->cubeSchema->getFields(),
        ));
    }

    public function relations(): RelationCollection
    {
        $relations = [];

        foreach ($this->cubeSchema->getRelations() as $name => $joinType) {
            $relations[] = $this->relationFromMethod($name, $joinType);
        }

        return new RelationCollection($relations);
    }

    /**
     * A field named after a column, formatted by the cast of that column.
     */
    private function fieldFromColumn(FieldType $type, string $column): FieldDefinitionDto
    {
        $format = FieldFormat::fromCast($this->model->getCasts()[$column] ?? null);

        return $type === FieldType::Metric
            ? FieldDefinitionDto::metric($column, format: $format ?? FieldFormat::Integer)
            : FieldDefinitionDto::dimension($column, format: $format ?? FieldFormat::Text);
    }

    /**
     * Read the keys of a relation method: "{this table}.{localKey} = {related table}.{foreignKey}".
     */
    private function relationFromMethod(string $name, JoinType $joinType): RelationDefinitionDto
    {
        $relation = method_exists($this->model, $name) ? $this->model->{$name}() : null;

        return match (true) {
            $relation instanceof BelongsTo => new RelationDefinitionDto(
                table: $relation->getRelated()->getTable(),
                localKey: $relation->getForeignKeyName(),
                foreignKey: $relation->getOwnerKeyName(),
                joinType: $joinType,
            ),
            $relation instanceof HasOneOrMany => new RelationDefinitionDto(
                table: $relation->getRelated()->getTable(),
                localKey: $relation->getLocalKeyName(),
                foreignKey: $relation->getForeignKeyName(),
                joinType: $joinType,
            ),
            default => throw CubesException::unsupportedRelation($this->model::class, $name),
        };
    }
}
