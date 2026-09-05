<?php

namespace Tanzar\Refract\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Tanzar\Refract\Splitter\Splitter;

class RefractOptimizer
{
    /**
     * @param Model $model
     * @return array<class-string<Splitter>>|null
     */
    public function getSplitters(Model $model): ?array
    {
        $map = self::getTrackableMap();

        $modelClass = $model::class;

        if (isset($map['splitters'][$modelClass])) {
            return $map['splitters'][$modelClass];
        }
        return null;
    }

    public function isTrackable(string $model): bool
    {
        $map = self::getTrackableMap();

        if (!isset($map['splitters'][$model])) {
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
    
    /**
     * @return array{splitters: array<string, non-empty-list<class-string<Splitter>>>, lens: array<mixed>}
     */
    public function getTrackableMap(): array
    {
        $cachePath = base_path('bootstrap/cache/refract_track_map.php');

        if (File::exists($cachePath)) {
            return File::getRequire($cachePath);
        }

        return self::discoverTrackMap();
    }

    /**
     * @return array{splitters: array<string, non-empty-list<class-string<Splitter>>>, lens: array<mixed>}
     */
    private function discoverTrackMap(): array
    {
        $namespace = config('refract.discovery.namespace', 'App\\');
        $path = config('refract.discovery.path', app_path());

        $classes = $this->scanDirForClasses($path, $namespace);

        return $this->makeOptimizerMap($classes);
    }

    /**
     * @param string $path
     * @param string $baseNamespace
     * @return class-string[]
     */
    private function scanDirForClasses(string $path, string $baseNamespace): array
    {
        $realPath = realpath($path);
        if (!is_dir($path) || $realPath === false) {
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

            if (file_exists($file->getRealPath())) {
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
     * @return array{splitters: array<string, non-empty-list<class-string<Splitter>>>, lens: array<mixed>}
     */
    private function makeOptimizerMap(array $classes): array
    {
        $map = [
            'splitters' => [],
            'lens' => []
        ];

        foreach ($classes as $className) {
            $reflection = new ReflectionClass($className);

            if ($reflection->isSubclassOf(Splitter::class) && $reflection->isInstantiable()) {
                /** @var class-string<Splitter> $className */
                $model = $className::modelClass();

                $modelSplitters = $map['splitters'][$model] ?? [];

                if (!in_array($className, $modelSplitters)) {
                    $modelSplitters[] = $className;
                    $map['splitters'][$model] = $modelSplitters;
                }

                //@TODO lens detection
            }
        }
        return $map;
    }

}
