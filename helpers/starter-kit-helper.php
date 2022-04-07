<?php

/**
 * @author James Carlo Luchavez <jamescarlo.luchavez@fligno.com>
 * @since  2021-11-09
 */

use Composer\Autoload\ClassMapGenerator;
use Fligno\StarterKit\ExtendedResponse;
use Fligno\StarterKit\StarterKit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Process\Process;

if (! function_exists('starterKit')) {
    /**
     * @return StarterKit
     */
    function starterKit(): StarterKit
    {
        return resolve('starter-kit');
    }
}

if (! function_exists('starter_kit')) {
    /**
     * @return StarterKit
     */
    function starter_kit(): StarterKit
    {
        return starterKit();
    }
}

if (! function_exists('custom_response')) {
    /**
     * @return ExtendedResponse
     */
    function custom_response(): ExtendedResponse
    {
        return resolve('extended-response');
    }
}

if (! function_exists('customResponse')) {
    /**
     * @return ExtendedResponse
     */
    function customResponse(): ExtendedResponse
    {
        return custom_response();
    }
}

if (! function_exists('array_filter_recursive')) {
    /**
     * @param  array $arr
     * @param  bool  $accept_boolean
     * @param  bool  $accept_null
     * @param  bool  $accept_0
     * @return array
     */
    function array_filter_recursive(
        array $arr,
        bool $accept_boolean = false,
        bool $accept_null = false,
        bool $accept_0 = false
    ): array {
        $result = [];
        foreach ($arr as $key => $value) {
            if (($accept_boolean && is_bool($value)) ||
                ($accept_0 && is_numeric($value) && (int)$value === 0) ||
                empty($value) === false ||
                ($accept_null && is_null($value))
            ) {
                if (is_array($value)) {
                    $result[$key] = array_filter_recursive($value, $accept_boolean, $accept_null, $accept_0);
                } else {
                    $result[$key] = $value;
                }
            }
        }

        return $result;
    }
}

if (! function_exists('arrayFilterRecursive')) {
    /**
     * @param  array $arr
     * @param  bool  $accept_boolean
     * @param  bool  $accept_null
     * @param  bool  $accept_0
     * @return array
     */
    function arrayFilterRecursive(
        array $arr,
        bool $accept_boolean = false,
        bool $accept_null = false,
        bool $accept_0 = false
    ): array {
        return array_filter_recursive($arr, $accept_boolean, $accept_null, $accept_0);
    }
}

if (! function_exists('is_request_instance')) {

    /**
     * @param  $request
     * @return bool
     */
    function is_request_instance($request): bool
    {
        return is_subclass_of($request, Request::class);
    }
}

if (! function_exists('isRequestInstance')) {

    /**
     * @param  $request
     * @return bool
     */
    #[Pure] function isRequestInstance($request): bool
    {
        return is_request_instance($request);
    }
}

if (! function_exists('request_or_array_has')) {
    /**
     * Check if the Request or associative array has a specific key.
     *
     * @param  array|Request $request
     * @param  string        $key
     * @param  bool|null     $is_exact
     * @return bool
     */
    function request_or_array_has(array|Request $request, string $key = '', ?bool $is_exact = true): bool
    {
        if (is_array($request) && (empty($request) || Arr::isAssoc($request))) {
            if ($is_exact) {
                return Arr::has($request, $key);
            }

            return (bool)preg_grep("/$key/", array_keys($request));
        }

        if (is_subclass_of($request, Request::class)) {
            if ($is_exact) {
                return $request->has($key);
            }

            return (bool)preg_grep("/$key/", $request->keys());
        }

        return false;
    }
}

if (! function_exists('requestOrArrayHas')) {
    /**
     * Check if the Request or associative array has a specific key.
     *
     * @param  array|Request $request
     * @param  string        $key
     * @param  bool|null     $is_exact
     * @return bool
     */
    function requestOrArrayHas(array|Request $request, string $key = '', ?bool $is_exact = true): bool
    {
        return request_or_array_has($request, $key, $is_exact);
    }
}

if (! function_exists('request_or_array_get')) {
    /**
     * Get a value from Request or associative array using a string key.
     *
     * @param  array|Request $request
     * @param  string        $key
     * @param  mixed|null    $default
     * @return mixed
     */
    function request_or_array_get(array|Request $request, string $key, mixed $default = null): mixed
    {
        if (request_or_array_has($request, $key)) {
            if (is_array($request)) {
                return $request[$key];
            }

            return $request->$key;
        }

        return $default;
    }
}

if (! function_exists('requestOrArrayGet')) {
    /**
     * Get a value from Request or associative array using a string key.
     *
     * @param  array|Request $request
     * @param  string        $key
     * @param  mixed|null    $default
     * @return mixed
     */
    function requestOrArrayGet(array|Request $request, string $key, mixed $default = null): mixed
    {
        return request_or_array_get($request, $key, $default);
    }
}

if (! function_exists('is_request_or_array_filled')) {
    /**
     * Check if a key exists and is not empty on a Request or associative array.
     *
     * @param  array|Request $request
     * @param  string        $key
     * @return bool
     */
    function is_request_or_array_filled(array|Request $request, string $key): bool
    {
        if (request_or_array_has($request, $key)) {
            if (is_array($request)) {
                return Arr::isFilled($request, $key);
            }

            return $request->filled($key);
        }

        return false;
    }
}

if (! function_exists('isRequestOrArrayFilled')) {
    /**
     * Check if a key exists and is not empty on a Request or associative array.
     *
     * @param  array|Request $request
     * @param  string        $key
     * @return bool
     */
    function isRequestOrArrayFilled(array|Request $request, string $key): bool
    {
        return is_request_or_array_filled($request, $key);
    }
}

if (! function_exists('is_eloquent_model')) {
    /**
     * Determine if the class using the trait is a subclass of Eloquent Model.
     *
     * @param  mixed $object_or_class
     * @return bool
     */
    function is_eloquent_model(mixed $object_or_class): bool
    {
        return is_subclass_of($object_or_class, Model::class);
    }
}

if (! function_exists('isEloquentModel')) {
    /**
     * Determine if the class using the trait is a subclass of Eloquent Model.
     *
     * @param  mixed $object_or_class
     * @return bool
     */
    #[Pure] function isEloquentModel(mixed $object_or_class): bool
    {
        return is_eloquent_model($object_or_class);
    }
}

if (! function_exists('get_class_name_from_object')) {
    /**
     * @param  mixed $object_or_class
     * @return mixed
     */
    function get_class_name_from_object(mixed $object_or_class): mixed
    {
        return is_object($object_or_class) ? get_class($object_or_class) : $object_or_class;
    }
}

if (! function_exists('getClassNameFromObject')) {
    /**
     * @param  mixed $object_or_class
     * @return mixed
     */
    #[Pure] function getClassNameFromObject(mixed $object_or_class): mixed
    {
        return get_class_name_from_object($object_or_class);
    }
}

/**
 * COLLECTION-RELATED
**/

if (! function_exists('collection_decode')) {
    /**
     * Decode a string to a Collection instance.
     *
     * @param  string|null $collection
     * @return Collection|string|null
     * @throws JsonException
     */
    function collection_decode(?string $collection): string|Collection|null
    {
        if ($collection) {
            $temp = json_decode($collection, true, 512, JSON_THROW_ON_ERROR);

            if (json_last_error() === JSON_ERROR_NONE) {
                return collect($temp);
            }
        }

        return $collection;
    }
}

if (! function_exists('collectionDecode')) {
    /**
     * Decode a string to a Collection instance.
     *
     * @param  string|null $collection
     * @return Collection|string|null
     * @throws JsonException
     */
    function collectionDecode(?string $collection): string|Collection|null
    {
        return collection_decode($collection);
    }
}

if (! function_exists('collection_encode')) {
    /**
     * Decode a string to a Collection instance.
     *
     * @param  Collection|null $collection
     * @return false|Collection|string|null
     * @throws JsonException
     */
    function collection_encode(?Collection $collection): bool|string|Collection|null
    {
        if ($collection) {
            $temp = json_encode($collection, JSON_THROW_ON_ERROR);

            if (json_last_error() === JSON_ERROR_NONE) {
                return $temp;
            }
        }

        return $collection;
    }
}

if (! function_exists('collectionEncode')) {
    /**
     * Decode a string to a Collection instance.
     *
     * @param  Collection|null $collection
     * @return false|Collection|string|null
     * @throws JsonException
     */
    function collectionEncode(?Collection $collection): bool|string|Collection|null
    {
        return collection_encode($collection);
    }
}

// Filesystem

if (! function_exists('collect_files_or_directories')) {
    /**
     * @param  string|null $directory
     * @param  bool        $withDirectories
     * @param  bool        $withFiles
     * @param  bool        $prependDirectory
     * @return Collection|null
     */
    function collect_files_or_directories(
        string $directory = null,
        bool $withDirectories = true,
        bool $withFiles = true,
        bool $prependDirectory = false
    ): ?Collection {
        $directory = trim($directory);

        if ($directory && ($withDirectories || $withFiles) && $arr = scandir($directory)) {
            $arr = collect($arr)->filter(fn($value) => ! Str::of($value)->startsWith('.'));

            if (! $withFiles) {
                $arr = $arr->filter(fn($value) => ! is_file($directory . '/' . $value));
            }

            if (! $withDirectories) {
                $arr = $arr->filter(fn($value) => ! is_dir($directory . '/' . $value));
            }

            if ($arr->isEmpty()) {
                return null;
            }

            if ($prependDirectory) {
                $arr = $arr->mapWithKeys(fn($value) => [$value => $directory . '/' . $value]);
            }

            return $arr;
        }

        return null;
    }
}

if (! function_exists('collectFilesOrDirectories')) {
    /**
     * @param  string|null $directory
     * @param  bool        $withDirectories
     * @param  bool        $withFiles
     * @param  bool        $prependDirectory
     * @return Collection|null
     */
    function collectFilesOrDirectories(
        string $directory = null,
        bool $withDirectories = true,
        bool $withFiles = true,
        bool $prependDirectory = false
    ): ?Collection {
        return collect_files_or_directories($directory, $withDirectories, $withFiles, $prependDirectory);
    }
}

if (! function_exists('get_dir_from_object_class_dir')) {
    /**
     * @param  object|string $objectOrClassOrDir
     * @return false|object|string
     */
    function get_dir_from_object_class_dir(object|string $objectOrClassOrDir): object|bool|string
    {
        $dir = $objectOrClassOrDir;

        if (is_object($objectOrClassOrDir) || (! is_dir($objectOrClassOrDir) && ! is_file($objectOrClassOrDir))) {
            try {
                if ($class = (new ReflectionClass($objectOrClassOrDir))) {
                    $dir = $class->getFileName();
                }
            } catch (ReflectionException) {
            }
        }

        if (! is_dir($dir)) {
            $dir = dirname($dir);
        }

        return str_replace('\\', '/', $dir);
    }
}

if (! function_exists('getDirFromObjectClassDir')) {
    /**
     * @param  object|string $objectOrClassOrDir
     * @return false|object|string
     */
    function getDirFromObjectClassDir(object|string $objectOrClassOrDir): object|bool|string
    {
        return get_dir_from_object_class_dir($objectOrClassOrDir);
    }
}

if (! function_exists('guess_file_or_directory_path')) {
    /**
     * @param  object|string           $sourceObjectOrClassOrDir
     * @param  Collection|array|string $targetFileOrFolder
     * @param  bool                    $traverseUp
     * @param  int                     $maxLevels
     * @return Collection|array|string|null
     */
    function guess_file_or_directory_path(
        object|string $sourceObjectOrClassOrDir,
        Collection|array|string $targetFileOrFolder,
        bool $traverseUp = false,
        int $maxLevels = 3
    ): array|string|Collection|null {
        $dir = get_dir_from_object_class_dir($sourceObjectOrClassOrDir);

        $targets = collect($targetFileOrFolder);

        $result = collect();

        $addToResult = static function (string $dir, $fileOrFolder, Collection $result) {
            if ($exists = file_exists($temp = $dir . '/' . $fileOrFolder)) {
                $result->put($fileOrFolder, $temp);
            }

            return $exists;
        };

        // For level 0

        $targets = $targets->filter(
            function ($value) use ($addToResult, $dir, $result) {
                return ! $addToResult($dir, $value, $result);
            }
        );

        if ($targets->count()) {
            // For upward folder traversal
            if ($traverseUp) {
                for ($level = 1; $targets->count() && $level <= $maxLevels; $level++) {
                    $targets = $targets->filter(
                        function ($value) use ($addToResult, $level, $dir, $result) {
                            return ! $addToResult(dirname($dir, $level), $value, $result);
                        }
                    );
                }
            } else { // For downward folder traversal
                $directories = collect($dir);
                for ($level = 1; $targets->count() && $level <= $maxLevels; $level++) {
                    $directories = $directories->mapWithKeys(
                        function ($value) use ($result, $addToResult, &$targets) {
                            if ($targets->count()) {
                                $subDirs = collect_files_or_directories($value, true, false, true) ?? collect();
                                if ($subDirs->count()) {
                                    $targets = $targets->filter(
                                        function ($value) use ($addToResult, $subDirs, $result) {
                                            foreach ($subDirs as $directory) {
                                                if ($addToResult($directory, $value, $result)) {
                                                    return false;
                                                }
                                            }
                                            return true;
                                        }
                                    );
                                }
                            }

                            return [];
                        }
                    );
                }
            }
        }

        if (is_string($targetFileOrFolder)) {
            return $result->first();
        }

        if (is_array($targetFileOrFolder)) {
            return $result->toArray();
        }

        return $result;
    }
}

if (! function_exists('guessFileOrDirectoryPath')) {
    /**
     * @param  object|string              $sourceObjectOrClassOrDir
     * @param  Collection|string[]|string $targetFileOrFolder
     * @param  bool                       $traverseUp
     * @param  int                        $maxLevels
     * @return string|null
     */
    function guessFileOrDirectoryPath(
        object|string $sourceObjectOrClassOrDir,
        Collection|array|string $targetFileOrFolder,
        bool $traverseUp = false,
        int $maxLevels = 3
    ): ?string {
        return guess_file_or_directory_path($sourceObjectOrClassOrDir, $targetFileOrFolder, $traverseUp, $maxLevels);
    }
}

if (!function_exists('collect_classes_from_path')) {
    /**
     * @param  string      $path
     * @param  string|null $suffix
     * @return Collection|null
     */
    function collect_classes_from_path(string $path, string $suffix = null): ?Collection
    {
        if (! file_exists($path)) {
            return null;
        }

        return collect(ClassMapGenerator::createMap($path))
            ->mapWithKeys(
                function ($item, $key) use ($suffix) {
                    if ($suffix) {
                        $item = Str::of($key)->afterLast('\\')->before($suffix)->jsonSerialize();
                    }

                    return [$item => $key];
                }
            );
    }
}

if (!function_exists('collectClassesFromPath')) {
    /**
     * @param  string      $path
     * @param  string|null $suffix
     * @return Collection
     */
    function collectClassesFromPath(string $path, string $suffix = null): Collection
    {
        return collect_classes_from_path($path, $suffix);
    }
}

// Validate Base64 String

if (! function_exists('is_valid_base64')) {
    /**
     * @param  string $string
     * @return bool
     */
    function is_valid_base64(string $string): bool
    {
        // Check if there are valid base64 characters
        if (!preg_match('/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $string)) {
            return false;
        }

        // Decode the string in strict mode and check the results
        $decoded = base64_decode($string, true);
        if (false === $decoded) {
            return false;
        }

        // Encode the string again
        if (base64_encode($decoded) !== $string) {
            return false;
        }

        return true;
    }
}

if (! function_exists('isValidBase64')) {
    /**
     * @param  string $string
     * @return bool
     */
    function isValidBase64(string $string): bool
    {
        return is_valid_base64($string);
    }
}

// Validate URL

if (! function_exists('is_valid_url')) {
    /**
     * @param  string $url
     * @return bool
     */
    function is_valid_url(string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH);
        $encoded_path = array_map('urlencode', explode('/', $path));
        $url = str_replace($path, implode('/', $encoded_path), $url);

        return (bool) filter_var($url, FILTER_VALIDATE_URL);
    }
}

if (! function_exists('isValidURL')) {
    /**
     * @param  string $url
     * @return bool
     */
    function isValidURL(string $url): bool
    {
        return is_valid_url($url);
    }
}


// Symfony Process

if (! function_exists('make_process')) {
    /**
     * @param  Collection|array $arguments
     * @param  string|null      $workingDirectory
     * @return Process
     */
    function make_process(Collection|array $arguments, string $workingDirectory = null): Process
    {
        if (! $workingDirectory) {
            $workingDirectory = base_path();
        }

        if ($arguments instanceof Collection) {
            $arguments = $arguments->toArray();
        }

        return new Process($arguments, $workingDirectory);
    }
}

if (! function_exists('makeProcess')) {
    /**
     * @param  Collection|array $arguments
     * @param  string|null      $workingDirectory
     * @return Process
     */
    function makeProcess(Collection|array $arguments, string $workingDirectory = null): Process
    {
        return  make_process($arguments, $workingDirectory);
    }
}

// Polymorphic Map

if (! function_exists('enforce_morph_map')) {
    /**
     * Define the morph map for polymorphic relations and require all morphed models to be explicitly mapped.
     *
     * @param array $map
     * @param bool  $merge
     */
    function enforce_morph_map(array $map, bool $merge = true): void
    {
        Relation::enforceMorphMap($map, $merge);
    }
}

if (! function_exists('enforceMorphMap')) {
    /**
     * Define the morph map for polymorphic relations and require all morphed models to be explicitly mapped.
     *
     * @param array $map
     * @param bool  $merge
     */
    function enforceMorphMap(array $map, bool $merge = true): void
    {
        enforce_morph_map($map, $merge);
    }
}

// Class Uses Trait

if (! function_exists('class_uses_trait')) {
    /**
     * @param  object|string $class
     * @param  string        $trait
     * @return bool
     */
    function class_uses_trait(object|string $class, string $trait): bool
    {
        return collect(class_uses_recursive($class))->contains($trait);
    }
}

if (! function_exists('classUsesTrait')) {
    /**
     * @param  object|string $class
     * @param  string        $trait
     * @return bool
     */
    function classUsesTrait(object|string $class, string $trait): bool
    {
        return class_uses_trait($class, $trait);
    }
}
