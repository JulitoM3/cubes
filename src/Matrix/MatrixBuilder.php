<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Matrix;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Collection;
use Julitom3\Cubes\Definitions\DTOs\DimensionDefinitionDto;
use Julitom3\Cubes\Definitions\DTOs\ReportDefinitionDto;
use Julitom3\Cubes\Definitions\DTOs\ValueDefinitionDto;
use Julitom3\Cubes\Definitions\QueryDefinition;

final readonly class MatrixBuilder
{
    public function __construct(private Translator $translator) {}

    /**
     * @param  Collection<int, array<string, mixed>>  $records  Query records keyed by the query definition aliases.
     */
    public function build(ReportDefinitionDto $report, Collection $records): Matrix
    {
        $rowDimensions = $report->rows;
        $columnDimensions = $report->columns;
        $measures = $report->values;

        /** @var array<string, list<mixed>> $rowKeys */
        $rowKeys = [];
        /** @var array<string, list<mixed>> $columnKeys */
        $columnKeys = [];
        /** @var array<string, array<string, array<int, mixed>>> $values */
        $values = [];

        foreach ($records as $record) {
            $rowValues = $this->dimensionValues($record, 0, count($rowDimensions));
            $columnValues = $this->dimensionValues($record, count($rowDimensions), count($columnDimensions));

            $rowKey = $this->key($rowValues);
            $columnKey = $this->key($columnValues);

            $rowKeys[$rowKey] ??= $rowValues;
            $columnKeys[$columnKey] ??= $columnValues;

            foreach (array_keys($measures) as $position) {
                $values[$rowKey][$columnKey][$position] = $record[QueryDefinition::valueAlias($position)] ?? null;
            }
        }

        // Records are ordered by row dimensions first, so column combinations need their own sort.
        $columnLeaves = array_values($columnKeys);
        usort($columnLeaves, fn (array $a, array $b): int => $a <=> $b);

        if ($columnDimensions === []) {
            $columnLeaves = [[]];
        }

        return new Matrix(
            headers: $this->headers($rowDimensions, $columnDimensions, $measures, $columnLeaves),
            rows: $this->rows($rowKeys, $columnLeaves, $measures, $values),
        );
    }

    /**
     * @param  list<DimensionDefinitionDto>  $rowDimensions
     * @param  list<DimensionDefinitionDto>  $columnDimensions
     * @param  list<ValueDefinitionDto>  $measures
     * @param  list<list<mixed>>  $columnLeaves
     * @return list<list<HeaderCell>>
     */
    private function headers(array $rowDimensions, array $columnDimensions, array $measures, array $columnLeaves): array
    {
        $showMeasureRow = count($measures) > 1 || ($columnDimensions === [] && $measures !== []);
        $height = max(1, count($columnDimensions) + ($showMeasureRow ? 1 : 0));
        $measureSpan = max(1, count($measures));

        /** @var list<list<HeaderCell>> $headers */
        $headers = array_fill(0, $height, []);

        foreach ($rowDimensions as $dimension) {
            $headers[0][] = new HeaderCell($dimension->label(), HeaderCell::DIMENSION, rowspan: $height);
        }

        foreach (array_keys($columnDimensions) as $level) {
            foreach ($this->groups($columnLeaves, $level) as [$value, $leaves]) {
                $headers[$level][] = new HeaderCell(
                    label: $this->label($value),
                    kind: HeaderCell::COLUMN,
                    colspan: $leaves * $measureSpan,
                    value: $value,
                );
            }
        }

        if ($showMeasureRow) {
            foreach ($columnLeaves as $leaf) {
                foreach ($measures as $measure) {
                    $headers[$height - 1][] = new HeaderCell($measure->label(), HeaderCell::MEASURE);
                }
            }
        }

        return $headers;
    }

    /**
     * Group consecutive leaves that share the same values up to the given level.
     *
     * @param  list<list<mixed>>  $leaves
     * @return list<array{mixed, int}>
     */
    private function groups(array $leaves, int $level): array
    {
        $values = [];
        $counts = [];
        $previous = null;

        foreach ($leaves as $leaf) {
            $prefix = $this->key(array_slice($leaf, 0, $level + 1));

            if ($prefix !== $previous) {
                $values[] = $leaf[$level];
                $counts[] = 0;
                $previous = $prefix;
            }

            $counts[count($counts) - 1]++;
        }

        return array_map(fn (mixed $value, int $count): array => [$value, $count], $values, $counts);
    }

    /**
     * @param  array<string, list<mixed>>  $rowKeys
     * @param  list<list<mixed>>  $columnLeaves
     * @param  list<ValueDefinitionDto>  $measures
     * @param  array<string, array<string, array<int, mixed>>>  $values
     * @return list<MatrixRow>
     */
    private function rows(array $rowKeys, array $columnLeaves, array $measures, array $values): array
    {
        $rows = [];

        foreach ($rowKeys as $rowKey => $rowValues) {
            $cells = [];

            foreach ($columnLeaves as $leaf) {
                $columnKey = $this->key($leaf);

                foreach ($measures as $position => $measure) {
                    $cells[] = [
                        'value' => $values[$rowKey][$columnKey][$position] ?? null,
                        'format' => $measure->field->format->value,
                    ];
                }
            }

            $rows[] = new MatrixRow(
                headers: array_map(fn (mixed $value): array => [
                    'label' => $this->label($value),
                    'value' => $value,
                ], $rowValues),
                cells: $cells,
            );
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $record
     * @return list<mixed>
     */
    private function dimensionValues(array $record, int $offset, int $count): array
    {
        $values = [];

        for ($position = $offset; $position < $offset + $count; $position++) {
            $values[] = $record[QueryDefinition::dimensionAlias($position)] ?? null;
        }

        return $values;
    }

    /**
     * @param  list<mixed>  $values
     */
    private function key(array $values): string
    {
        return (string) json_encode($values);
    }

    private function label(mixed $value): string
    {
        if ($value === null || $value === '') {
            return (string) $this->translator->get('cubes::messages.empty');
        }

        return is_scalar($value) ? (string) $value : (string) json_encode($value);
    }
}
