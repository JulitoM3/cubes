<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Graph;

use Julitom3\Cubes\Datasets\DatasetRegistry;
use Julitom3\Cubes\Exceptions\CubesException;
use SplQueue;

/**
 * An undirected graph of datasets, where every relation can be walked in both directions.
 */
final class DatasetGraph
{
    /** @var array<string, list<Edge>> */
    private array $edges = [];

    public function __construct(DatasetRegistry $registry)
    {
        foreach ($registry->all() as $name => $dataset) {
            $this->edges[$name] ??= [];

            foreach ($dataset->relations() as $relation) {
                $target = $registry->forTable($relation->table);

                // Relations to tables without a registered dataset cannot be reported on.
                if ($target === null || $target->name() === $name) {
                    continue;
                }

                $this->edges[$name][] = new Edge(
                    from: $name,
                    to: $target->name(),
                    fromTable: $dataset->table(),
                    toTable: $target->table(),
                    localKey: $relation->localKey,
                    foreignKey: $relation->foreignKey,
                    type: $relation->type,
                );

                $this->edges[$target->name()][] = new Edge(
                    from: $target->name(),
                    to: $name,
                    fromTable: $target->table(),
                    toTable: $dataset->table(),
                    localKey: $relation->foreignKey,
                    foreignKey: $relation->localKey,
                    type: $relation->type,
                );
            }
        }
    }

    public function degree(string $dataset): int
    {
        return count($this->edges[$dataset] ?? []);
    }

    /**
     * The shortest sequence of joins from one dataset to another.
     *
     * @return list<Edge>
     */
    public function path(string $from, string $to): array
    {
        if ($from === $to) {
            return [];
        }

        /** @var SplQueue<string> $queue */
        $queue = new SplQueue;
        $queue->enqueue($from);

        /** @var array<string, Edge> $reachedThrough */
        $reachedThrough = [];
        $visited = [$from => true];

        while (! $queue->isEmpty()) {
            $current = $queue->dequeue();

            foreach ($this->edges[$current] ?? [] as $edge) {
                if (isset($visited[$edge->to])) {
                    continue;
                }

                $visited[$edge->to] = true;
                $reachedThrough[$edge->to] = $edge;

                if ($edge->to === $to) {
                    return $this->buildPath($reachedThrough, $from, $to);
                }

                $queue->enqueue($edge->to);
            }
        }

        throw CubesException::noPath($from, $to);
    }

    /**
     * @param  array<string, Edge>  $reachedThrough
     * @return list<Edge>
     */
    private function buildPath(array $reachedThrough, string $from, string $to): array
    {
        $path = [];

        for ($current = $to; $current !== $from; $current = $edge->from) {
            $edge = $reachedThrough[$current];
            $path[] = $edge;
        }

        return array_reverse($path);
    }
}
