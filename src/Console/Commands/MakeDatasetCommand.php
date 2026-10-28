<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Console\Commands;

use Illuminate\Console\Command;
use Julitom3\Cubes\Schema\DatasetWriter;
use Julitom3\Cubes\Schema\SchemaReader;

class MakeDatasetCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'cubes:make-dataset
        {table : The database table of the dataset}
        {--empty : Create the class without reading fields and relations from the schema}
        {--force : Overwrite the dataset if it already exists}';

    /**
     * The command description.
     */
    protected $description = 'Create a Cubes dataset class for a database table';

    /**
     * Execute the console command.
     */
    public function handle(SchemaReader $schema, DatasetWriter $writer): int
    {
        $table = $this->argument('table');
        $table = is_string($table) ? $table : '';

        if (! $schema->hasTable($table)) {
            $this->components->error((string) trans('cubes::messages.commands.table_not_found', ['table' => $table]));

            return self::FAILURE;
        }

        if ($writer->exists($table) && ! $this->option('force')) {
            $this->components->warn((string) trans('cubes::messages.commands.dataset_exists', ['path' => $writer->path($table)]));

            return self::FAILURE;
        }

        $path = $writer->write($table, withSchema: ! $this->option('empty'));

        $this->components->info((string) trans('cubes::messages.commands.dataset_created', ['path' => $path]));

        return self::SUCCESS;
    }
}
