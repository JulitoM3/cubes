<?php

declare(strict_types=1);

namespace Julitom3\Cubes\ReportEngine\Definitions\Factories\Contracts;

/**
 * Turn each item of a report definition into the definition of one query clause.
 *
 * @template TItem
 * @template TDefinition
 */
abstract class DefinitionFactoryContract
{
    /**
     * @param  list<TItem>  $items
     * @return list<TDefinition>
     */
    public function create(array $items): array
    {
        $definitions = [];

        foreach ($items as $position => $item) {
            $definitions[] = $this->createDefinition($item, $position);
        }

        return $definitions;
    }

    /**
     * @param  TItem  $item
     * @param  int  $position  The position of the item, used to alias selects.
     * @return TDefinition
     */
    abstract protected function createDefinition(mixed $item, int $position): mixed;
}
