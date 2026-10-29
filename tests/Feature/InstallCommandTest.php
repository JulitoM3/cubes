<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->published = [
        'config' => config_path('cubes.php'),
        'lang' => lang_path('vendor/cubes'),
    ];

    cleanPublishedFiles($this->published);
});

afterEach(function () {
    cleanPublishedFiles($this->published);
});

/**
 * @param  array<string, string>  $paths
 */
function cleanPublishedFiles(array $paths): void
{
    File::delete($paths['config']);
    File::deleteDirectory($paths['lang']);
}

it('publishes the configuration and the translations', function () {
    $this->artisan('cubes:install')
        ->expectsOutputToContain('cubes-config')
        ->expectsOutputToContain('cubes-lang')
        ->expectsOutputToContain('Cubes installed successfully.')
        ->expectsOutputToContain('Add the Cubable trait to your models')
        ->assertSuccessful();

    expect(File::exists($this->published['config']))->toBeTrue()
        ->and(File::exists("{$this->published['lang']}/en/messages.php"))->toBeTrue()
        ->and(File::exists("{$this->published['lang']}/es/messages.php"))->toBeTrue();
});

it('no longer offers the dataset stub', function () {
    $this->artisan('cubes:install', ['--with-stubs' => true]);
})->throws(InvalidArgumentException::class, 'The "--with-stubs" option does not exist.');

it('keeps published files unless forced', function () {
    File::put($this->published['config'], '<?php return [];');

    $this->artisan('cubes:install')->assertSuccessful();

    expect(File::get($this->published['config']))->toBe('<?php return [];');

    $this->artisan('cubes:install', ['--force' => true])->assertSuccessful();

    expect(File::get($this->published['config']))->toBe(File::get(__DIR__.'/../../config/cubes.php'));
});

it('translates its output', function () {
    app()->setLocale('es');

    $this->artisan('cubes:install')
        ->expectsOutputToContain('Cubes se instaló correctamente.')
        ->assertSuccessful();
});
