<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Console\Commands;

use Illuminate\Console\Command;
use Julitom3\Cubes\Datasets\DTOs\FieldDefinitionDto;
use Julitom3\Cubes\Datasets\DTOs\RelationDefinitionDto;
use Julitom3\Cubes\Datasets\ModelDataset;
use Julitom3\Cubes\ReportEngine\DatasetRegistry;

class ListCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'cubes:list';

    /**
     * The command description.
     */
    protected $description = 'List the models that can be used as Cubes datasets';

    /**
     * Execute the console command.
     */
    public function handle(DatasetRegistry $registry): int
    {
        if (config('cubes.auto_discover')) {
            /** @var list<string> $paths */
            $paths = config('cubes.discover_paths', []);

            $this->components->info((string) trans('cubes::messages.commands.auto_discover_enabled', ['paths' => implode(', ', $paths)]));
        } else {
            $this->components->info((string) trans('cubes::messages.commands.auto_discover_disabled'));
        }

        $datasets = $registry->all();

        if ($datasets === []) {
            $this->components->warn((string) trans('cubes::messages.commands.no_models'));
            $this->line((string) trans('cubes::messages.commands.next_steps'));

            return self::SUCCESS;
        }

        $this->line((string) trans('cubes::messages.commands.list'));

        $rows = [];

        foreach ($datasets as $dataset) {
            $fields = $dataset->fields();

            $rows[] = [
                $dataset instanceof ModelDataset ? $dataset->model()::class : '',
                $dataset->category(),
                $dataset->table(),
                $fields->filter(fn (FieldDefinitionDto $field): bool => $field->isDimension())->count(),
                $fields->reject(fn (FieldDefinitionDto $field): bool => $field->isDimension())->count(),
                $dataset->relations()->map(fn (RelationDefinitionDto $relation): string => $relation->table)->implode(', '),
            ];
        }

        /** @var list<string> $headers */
        $headers = trans('cubes::messages.commands.list_headers');

        $this->table($headers, $rows);

        return self::SUCCESS;
    }
}
