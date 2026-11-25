<?php

namespace Mallto\Admin\Domain\SubjectConfig;

use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class SubjectConfigRegistry
{
    private static array $modules = [];

    private static array $definitions = [];

    private static array $retiredKeys = [];

    public static function registerRetiredKeys(array $keys): void
    {
        foreach ($keys as $key) {
            self::$retiredKeys[$key] = true;
        }
    }

    public static function isRetired(string $key): bool
    {
        return isset(self::$retiredKeys[$key]);
    }

    public static function registerModule(string $key, array $module): void
    {
        if (isset(self::$modules[$key]) && self::$modules[$key] !== array_merge(['key' => $key], $module)) {
            self::handleDuplicate('module', $key);
        }

        self::$modules[$key] = array_merge([
            'key' => $key,
        ], $module);
    }

    public static function registerDefinitions(array $definitions): void
    {
        foreach ($definitions as $definition) {
            $key = (string)($definition['key'] ?? '');
            if ($key === '') {
                continue;
            }

            if (isset(self::$definitions[$key]) && self::$definitions[$key] !== $definition) {
                self::handleDuplicate('definition', $key);
            }

            self::$definitions[$key] = $definition;
        }
    }

    public static function modules(): array
    {
        return self::$modules;
    }

    public static function definitions(?string $module = null): array
    {
        if ($module === null) {
            return self::$definitions;
        }

        return array_filter(self::$definitions, function (array $definition) use ($module) {
            return ($definition['module'] ?? null) === $module;
        });
    }

    public static function definition(string $key): ?array
    {
        return self::$definitions[$key] ?? null;
    }

    private static function handleDuplicate(string $type, string $key): void
    {
        $message = sprintf('Duplicate subject config %s registered: %s', $type, $key);

        if (function_exists('app')) {
            try {
                if (app()->environment('local', 'testing')) {
                    throw new InvalidArgumentException($message);
                }
            } catch (InvalidArgumentException $exception) {
                throw $exception;
            } catch (\Throwable) {
                // If the container is not ready yet, fall through to logging.
            }
        }

        try {
            Log::warning($message);
        } catch (\Throwable) {
        }
    }
}
