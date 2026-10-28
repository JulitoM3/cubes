<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Console\Commands;

use Illuminate\Console\Command;
use Julitom3\Cubes\Schema\DatasetWriter;
use Julitom3\Cubes\Schema\SchemaReader;

class SyncDatasetsCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'cubes:sync
        {--force : Overwrite datasets that already exist}';

    /**
     * The command description.
     */
    protected $description = 'Create the dataset classes of the tables listed in cubes.generator.tables';

    /**
     * Execute the console command.
     */
    public function handle(SchemaReader $schema, DatasetWriter $writer): int
    {
        $tables = config('cubes.generator.tables', []);
        $tables = is_array($tables) ? array_filter($tables, is_string(...)) : [];

        if ($tables === []) {
            $this->components->warn((string) trans('cubes::messages.commands.no_tables'));

            return self::SUCCESS;
        }

        $created = 0;

        foreach ($tables as $table) {
            if (! $schema->hasTable($table)) {
                $this->components->warn((string) trans('cubes::messages.commands.table_not_found', ['table' => $table]));

                continue;
            }

            if ($writer->exists($table) && ! $this->option('force')) {
                $this->components->twoColumnDetail($table, (string) trans('cubes::messages.commands.skipped'));

                continue;
            }

            $writer->write($table);
            $created++;

            $this->components->twoColumnDetail($table, (string) trans('cubes::messages.commands.written'));
        }

        $this->components->info((string) trans('cubes::messages.commands.synced', ['count' => (string) $created]));

        return self::SUCCESS;
    }
}
