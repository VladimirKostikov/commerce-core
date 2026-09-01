<?php

namespace App\Services\Testing;

use App\Contracts\TestCatalogInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

final class PhpUnitTestCatalog implements TestCatalogInterface
{
    public function __construct(
        private readonly string $testsPath,
    ) {
    }

    public function cases(): array
    {
        $cases = [];

        foreach (KnownTestSuites::directories() as $suite => $directory) {
            foreach ($this->files($this->testsPath.DIRECTORY_SEPARATOR.$directory) as $file) {
                $class = $this->className($directory, $file);
                foreach ($this->methods($file) as $method) {
                    $cases[] = [
                        'suite' => $suite,
                        'class' => $class,
                        'method' => $method,
                        'id' => $class.'::'.$method,
                    ];
                }
            }
        }

        return $cases;
    }

    public function contains(string $id): bool
    {
        foreach ($this->cases() as $case) {
            if ($case['id'] === $id) {
                return true;
            }
        }

        return false;
    }

    private function files(string $directory): array
    {
        if (! is_dir($directory)) {
            return [];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

        foreach ($iterator as $file) {
            if ($file instanceof SplFileInfo && $file->isFile() && str_ends_with($file->getFilename(), 'Test.php')) {
                $files[] = $file->getPathname();
            }
        }

        sort($files);

        return $files;
    }

    private function className(string $suiteDirectory, string $path): string
    {
        $root = $this->testsPath.DIRECTORY_SEPARATOR.$suiteDirectory.DIRECTORY_SEPARATOR;
        $relative = substr($path, strlen($root));
        $trimmed = preg_replace('/\.php$/', '', str_replace(DIRECTORY_SEPARATOR, '\\', $relative));

        return 'Tests\\'.$suiteDirectory.'\\'.$trimmed;
    }

    private function methods(string $path): array
    {
        $source = file_get_contents($path);

        if (! is_string($source)) {
            return [];
        }

        preg_match_all('/public function (test\w+)\s*\(/', $source, $matches);

        return $matches[1] ?? [];
    }
}
