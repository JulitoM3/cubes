<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\DTOs\DatasetGraph;

/**
 * An ordered path of nodes from an origin to a destination, both included.
 */
final readonly class DatasetPathDto
{
    /**
     * @param  non-empty-list<DatasetNodeDto>  $nodes
     */
    public function __construct(
        public array $nodes,
    ) {}

    public function origin(): DatasetNodeDto
    {
        return $this->nodes[0];
    }

    public function destination(): DatasetNodeDto
    {
        return $this->nodes[count($this->nodes) - 1];
    }

    public function hops(): int
    {
        return count($this->nodes) - 1;
    }

    /**
     * Whether the path does not leave its origin.
     */
    public function isEmpty(): bool
    {
        return $this->hops() === 0;
    }

    /**
     * @return list<DatasetNodeDto>
     */
    public function intermediateNodes(): array
    {
        return array_slice($this->nodes, 1, -1);
    }

    public function contains(string $table): bool
    {
        foreach ($this->nodes as $node) {
            if ($node->table === $table) {
                return true;
            }
        }

        return false;
    }

    /**
     * The consecutive pairs of nodes, e.g. [[sales, stores], [stores, regions]].
     *
     * @return list<array{DatasetNodeDto, DatasetNodeDto}>
     */
    public function pairs(): array
    {
        $pairs = [];

        for ($index = 0; $index < $this->hops(); $index++) {
            $pairs[] = [$this->nodes[$index], $this->nodes[$index + 1]];
        }

        return $pairs;
    }
}
