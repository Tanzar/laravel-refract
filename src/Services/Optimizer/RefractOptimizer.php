<?php

namespace Tanzar\Refract\Services\Optimizer;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Finder\Finder;
use Tanzar\Refract\Helpers\RefractConfig;
use Tanzar\Refract\Splitter\Splitter;

final class RefractOptimizer
{
    private SplittersMap $splitters;

    public function __construct()
    {
        $map = $this->getCachedMap();
        $this->splitters = new SplittersMap($map['splitters'] ?? []);

        if ($map === null) {
            $this->discoverTrackMap();
        }
    }
   
    /**
     * @return array{splitters: array<class-string<Model>, array<class-string<Splitter>>>, lens: array<mixed>}
     */
    private function getCachedMap(): ?array
    {
        $cachePath = app()->bootstrapPath('cache/refract_track_map.php');

        if (File::exists($cachePath)) {
            return File::getRequire($cachePath);
        }

        return null;
    }

    /**
     * @return array{splitters: array<class-string<Model>, array<class-string<Splitter>>>, lens: array<mixed>}
     */
    private function discoverTrackMap(): array
    {
        $namespace = RefractConfig::discoverNamespace();
        $path = RefractConfig::discoverPath();

        $classes = $this->scanDirForClasses($path, $namespace);

        $this->makeOptimizerMap($classes);

        return [
            'splitters' => $this->splitters->toArray(),
            'lens' => []
        ];
    }

    /**
     * @param string $path
     * @param string $baseNamespace
     * @return class-string[]
     */
    private function scanDirForClasses(string $path, string $baseNamespace): array
    {
        $realPath = realpath($path);
        if (!File::isDirectory($path) || $realPath === false) {
            return [];
        }

        $classes = [];
        $baseNamespace = rtrim($baseNamespace, '\\') . '\\';
        $finder = (new Finder())->in($realPath)->files()->name('*.php');

        foreach ($finder as $file) {
            $relativePath = ltrim(substr($file->getRealPath(), strlen($realPath)), DIRECTORY_SEPARATOR);

            $classSubNamespace = str_replace(
                [DIRECTORY_SEPARATOR, '.php'], 
                ['\\', ''], 
                $relativePath
            );

            $className = $baseNamespace . $classSubNamespace;

            if (File::exists($file)) {
                include_once $file->getRealPath();
            }

            if (class_exists($className, false)) {
                $classes[] = $className;
            }
        }

        return $classes;
    }

     /**
     * @param array<class-string> $classes
     */
    private function makeOptimizerMap(array $classes): void
    {
        foreach ($classes as $className) {
            $added = $this->splitters->add($className);

            if (!$added) {
                //@TODO lens detection
            }
        }
    }
    public function isTrackable(string $model): bool
    {
        if (!$this->splitters->isTracked($model)) {
            return false;
        }

        if (!class_exists($model)) {
            Log::warning("RefractTracker: Model class {$model} does not exist.");
            return false;
        }

        if (!is_subclass_of($model, 'Illuminate\Database\Eloquent\Model')) {
            Log::warning("RefractTracker: Class {$model} is not an Eloquent model.");
            return false;
        }
        return true;
    }

    public function splitters(): SplittersMap
    {
        return $this->splitters;
    }

    public function saveCache(): void
    {
        $cachePath = app()->bootstrapPath('cache/refract_track_map.php');

        $map = [
            'splitters' => $this->splitters->toArray(),
            'lens' => []
        ];

        $fileContent = "<?php\n\nreturn " . var_export($map, true) . ";\n";

        File::put($cachePath, $fileContent);
    }
}
