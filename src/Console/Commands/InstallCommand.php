<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Console\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The command signature.
     */
    protected $signature = 'cubes:install
        {--force : Overwrite files that were already published}';

    /**
     * The command description.
     */
    protected $description = 'Publish the Cubes configuration and translations';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        foreach (['cubes-config', 'cubes-lang'] as $tag) {
            $this->callSilently('vendor:publish', [
                '--tag' => $tag,
                '--force' => (bool) $this->option('force'),
            ]);

            $this->components->twoColumnDetail($tag, (string) trans('cubes::messages.commands.published'));
        }

        $this->components->info((string) trans('cubes::messages.commands.installed'));
        $this->line((string) trans('cubes::messages.commands.next_steps'));

        return self::SUCCESS;
    }
}
