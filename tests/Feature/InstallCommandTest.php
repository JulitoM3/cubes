<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

beforeEach(function () {
    $this->published = [
        'config' => config_path('cubes.php'),
        'lang' => lang_path('vendor/cubes'),
        'stub' => base_path('stubs/cubes/dataset.stub'),
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
    File::delete($paths['stub']);
    File::deleteDirectory($paths['lang']);
    File::deleteDirectory(dirname($paths['stub']));
}

it('publishes the configuration and the translations without the stub', function () {
    $this->artisan('cubes:install')
        ->expectsOutputToContain('cubes-config')
        ->expectsOutputToContain('cubes-lang')
        ->doesntExpectOutputToContain('cubes-stubs')
        ->expectsOutputToContain('Cubes installed successfully.')
        ->assertSuccessful();

    expect(File::exists($this->published['config']))->toBeTrue()
        ->and(File::exists("{$this->published['lang']}/en/messages.php"))->toBeTrue()
        ->and(File::exists("{$this->published['lang']}/es/messages.php"))->toBeTrue()
        ->and(File::exists($this->published['stub']))->toBeFalse();
});

it('also publishes the dataset stub with --with-stubs', function () {
    $this->artisan('cubes:install', ['--with-stubs' => true])
        ->expectsOutputToContain('cubes-stubs')
        ->assertSuccessful();

    expect(File::get($this->published['stub']))->toBe(File::get(__DIR__.'/../../stubs/dataset.stub'));
});

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
