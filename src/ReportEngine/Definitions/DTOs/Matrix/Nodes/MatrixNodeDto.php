<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\Nodes;

use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\MatrixLevelDefinitionDto;
use Julitom3\Cubes\ReportEngine\Definitions\DTOs\Matrix\Tree\MatrixMetricDto;

/**
 * A node of a row or column tree. The root is a sentinel above the first level.
 */
final class MatrixNodeDto
{
    public const string ROOT = '__ROOT__';

    /**
     * @param  array<string, self>  $children  Keyed by the encoded value, so null, numbers, and strings never collide.
     * @param  array<string, list<MatrixMetricDto>>  $metrics  The metrics of a row leaf, keyed by the column leaf they belong to.
     */
    public function __construct(
        public readonly ?MatrixLevelDefinitionDto $matrixLevelDefinitionDto,
        public readonly mixed $value,
        public readonly ?self $parent = null,
        public array $children = [],
        public array $metrics = [],
    ) {}

    public static function root(): self
    {
        return new self(matrixLevelDefinitionDto: null, value: self::ROOT);
    }

    public function child(mixed $value): ?self
    {
        return $this->children[self::encode($value)] ?? null;
    }

    public function addChild(self $node): void
    {
        $this->children[self::encode($node->value)] = $node;
    }

    public function isLeaf(): bool
    {
        return $this->children === [];
    }

    public function hasChildren(): bool
    {
        return ! $this->isLeaf();
    }

    public function isRoot(): bool
    {
        return $this->parent === null;
    }

    /**
     * The level of the node, starting at 0; the root sits above the first level, at -1.
     */
    public function depth(): int
    {
        return $this->matrixLevelDefinitionDto === null ? -1 : $this->matrixLevelDefinitionDto->depth;
    }

    /**
     * The values from the first level down to this node; empty for the root.
     *
     * @return list<mixed>
     */
    public function path(): array
    {
        if ($this->parent === null) {
            return [];
        }

        return [...$this->parent->path(), $this->value];
    }

    /**
     * A stable key of the path, used to match row leaves with column leaves.
     */
    public function key(): string
    {
        return self::keyOf($this->path());
    }

    /**
     * The key of a path of values, as key() returns it for the node at the end of that path.
     *
     * @param  list<mixed>  $path
     */
    public static function keyOf(array $path): string
    {
        return self::encode($path);
    }

    /**
     * The leaves below the node, in order; a leaf node is its own only leaf.
     *
     * @return list<self>
     */
    public function leaves(): array
    {
        if ($this->isLeaf()) {
            return [$this];
        }

        $leaves = [];

        foreach ($this->children as $child) {
            $leaves = [...$leaves, ...$child->leaves()];
        }

        return $leaves;
    }

    /**
     * Sort the children of every level by value.
     */
    public function sortChildren(): void
    {
        uasort($this->children, fn (self $a, self $b): int => $a->value <=> $b->value);

        foreach ($this->children as $child) {
            $child->sortChildren();
        }
    }

    /**
     * @return list<MatrixMetricDto>|null
     */
    public function metricsFor(string $columnKey): ?array
    {
        return $this->metrics[$columnKey] ?? null;
    }

    private static function encode(mixed $value): string
    {
        return (string) json_encode($value);
    }
}
