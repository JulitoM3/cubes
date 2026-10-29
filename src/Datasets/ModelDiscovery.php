<?php

declare(strict_types=1);

namespace Julitom3\Cubes\Datasets;

use FilesystemIterator;
use Illuminate\Database\Eloquent\Model;
use Julitom3\Cubes\Traits\Cubable;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use SplFileInfo;

/**
 * Find the Cubable models declared in the given directories.
 *
 * The class of every PHP file is read from its namespace and class declarations, so it
 * does not depend on how the directories are mapped in composer.json.
 */
final readonly class ModelDiscovery
{
    /**
     * @param  list<string>  $paths
     * @return list<class-string<Model>>
     */
    public function discover(array $paths): array
    {
        $models = [];

        foreach ($paths as $path) {
            if (! is_dir($path)) {
                continue;
            }

            /** @var iterable<SplFileInfo> $files */
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS));

            foreach ($files as $file) {
                if ($file->getExtension() !== 'php') {
                    continue;
                }

                $class = $this->classFromFile($file->getPathname());

                if ($class !== null && $this->isCubable($class)) {
                    $models[$class] = $class;
                }
            }
        }

        // Directory order depends on the filesystem, so sort to register models deterministically.
        ksort($models);

        return array_values($models);
    }

    /**
     * The fully qualified name of the first class declared in the file.
     */
    private function classFromFile(string $path): ?string
    {
        $tokens = token_get_all((string) file_get_contents($path));
        $namespace = '';

        foreach ($tokens as $index => $token) {
            if (! is_array($token)) {
                continue;
            }

            if ($token[0] === T_NAMESPACE) {
                $namespace = $this->nextName($tokens, $index) ?? '';
            }

            if ($token[0] === T_CLASS && $this->declaresClass($tokens, $index)) {
                $class = $this->nextName($tokens, $index);

                return $class === null ? null : ltrim("{$namespace}\\{$class}", '\\');
            }
        }

        return null;
    }

    /**
     * @param  array<int, array{int, string, int}|string>  $tokens
     */
    private function nextName(array $tokens, int $index): ?string
    {
        for ($next = $index + 1; isset($tokens[$next]); $next++) {
            $token = $tokens[$next];

            if (is_array($token) && in_array($token[0], [T_STRING, T_NAME_QUALIFIED], true)) {
                return $token[1];
            }

            if (! is_array($token) || $token[0] !== T_WHITESPACE) {
                return null;
            }
        }

        return null;
    }

    /**
     * Whether the class keyword starts a declaration, not "Foo::class" or "new class".
     *
     * @param  array<int, array{int, string, int}|string>  $tokens
     */
    private function declaresClass(array $tokens, int $index): bool
    {
        for ($previous = $index - 1; $previous >= 0; $previous--) {
            $token = $tokens[$previous];

            if (is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                continue;
            }

            return ! (is_array($token) && in_array($token[0], [T_DOUBLE_COLON, T_NEW], true));
        }

        return true;
    }

    /**
     * @phpstan-assert-if-true class-string<Model> $class
     */
    private function isCubable(string $class): bool
    {
        if (! class_exists($class) || ! is_subclass_of($class, Model::class)) {
            return false;
        }

        return ! (new ReflectionClass($class))->isAbstract()
            && in_array(Cubable::class, class_uses_recursive($class), true);
    }
}
