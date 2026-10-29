<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Schema;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Julitom3\Cubes\Datasets\Enums\FieldFormat;

final readonly class DatasetWriter
{
    public function __construct(
        private Filesystem $files,
        private SchemaReader $schema,
        private Repository $config,
        private Application $app,
    ) {}

    public function className(string $table): string
    {
        return Str::studly($table).'Dataset';
    }

    public function path(string $table): string
    {
        $directory = $this->config->get('cubes.generator.path');
        $directory = is_string($directory) ? $directory : $this->app->path('Cubes/Datasets');

        return rtrim($directory, '/\\').DIRECTORY_SEPARATOR.$this->className($table).'.php';
    }

    public function exists(string $table): bool
    {
        return $this->files->exists($this->path($table));
    }

    /**
     * Write the dataset class of the table, reading its fields and relations from the schema when asked.
     */
    public function write(string $table, bool $withSchema = true): string
    {
        $path = $this->path($table);

        $this->files->ensureDirectoryExists(dirname($path));
        $this->files->put($path, $this->render($table, $withSchema));

        return $path;
    }

    public function render(string $table, bool $withSchema = true): string
    {
        $namespace = $this->config->get('cubes.generator.namespace');

        return str_replace(
            ['{{ namespace }}', '{{ class }}', '{{ table }}', '{{ fields }}', '{{ relations }}'],
            [
                is_string($namespace) ? $namespace : 'App\\Cubes\\Datasets',
                $this->className($table),
                $this->literal($table),
                $withSchema ? $this->fields($table) : '',
                $withSchema ? $this->relations($table) : '',
            ],
            $this->files->get($this->stubPath()),
        );
    }

    private function fields(string $table): string
    {
        $lines = [];

        foreach ($this->schema->columns($table) as $column) {
            $format = FieldFormat::fromColumnType($column['type_name']);

            $lines[] = sprintf(
                '            FieldDefinitionDto::dimension(%s, %s, FieldFormat::%s),',
                $this->literal($column['name']),
                $this->literal(Str::headline($column['name'])),
                $format->name,
            );
        }

        return $this->block($lines);
    }

    private function relations(string $table): string
    {
        $lines = [];

        foreach ($this->schema->relations($table) as $relation) {
            $lines[] = sprintf(
                '            new RelationDefinitionDto(%s, %s, %s),',
                $this->literal($relation->table),
                $this->literal($relation->localKey),
                $this->literal($relation->foreignKey),
            );
        }

        return $this->block($lines);
    }

    /**
     * Quote a value as a single-quoted PHP string literal.
     */
    private function literal(string $value): string
    {
        return "'".addcslashes($value, "'\\")."'";
    }

    /**
     * @param  list<string>  $lines
     */
    private function block(array $lines): string
    {
        return $lines === [] ? '' : PHP_EOL.implode(PHP_EOL, $lines).PHP_EOL.'        ';
    }

    private function stubPath(): string
    {
        $published = $this->app->basePath('stubs/cubes/dataset.stub');

        return $this->files->exists($published) ? $published : __DIR__.'/../../stubs/dataset.stub';
    }
}
