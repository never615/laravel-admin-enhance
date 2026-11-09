<?php

namespace Mallto\Admin\Domain\SubjectConfig;

use Mallto\Admin\Data\SubjectConfig;
use Mallto\Tool\Domain\RuntimeConfig\RuntimeConfigCache;
use Mallto\Tool\Domain\RuntimeConfig\RuntimeConfigIdentity;
use Mallto\Tool\Domain\RuntimeConfig\RuntimeConfigInvalidator;

class SubjectConfigLiveCache
{
    public function __construct(
        private readonly RuntimeConfigCache $cache,
        private readonly RuntimeConfigInvalidator $invalidator
    ) {
    }

    /**
     * Backward-compatible private-scope adapter.
     *
     * @param callable(): array{found: bool, value: mixed} $loader
     * @return array{found: bool, value: mixed, source: string}
     */
    public function remember(int $subjectId, string $key, callable $loader): array
    {
        $loaded = false;
        $result = $this->rememberPrivate(
            $subjectId,
            $key,
            SubjectConfigDefinitions::applyModeFor($key),
            static function () use ($loader, &$loaded): array {
                $loaded = true;

                return $loader();
            }
        );

        return $result + ['source' => $loaded ? 'database' : 'cache'];
    }

    /** @param callable(): array{found: bool, value: mixed} $loader */
    public function rememberPrivate(int $subjectId, string $key, string $mode, callable $loader): array
    {
        return $this->cache->remember(RuntimeConfigIdentity::subjectPrivate($subjectId, $key), $mode, $loader);
    }

    /** @param callable(): array{found: bool, value: mixed} $loader */
    public function rememberPublic(int $subjectId, string $key, string $mode, callable $loader): array
    {
        return $this->cache->remember(RuntimeConfigIdentity::subjectPublic($subjectId, $key), $mode, $loader);
    }

    /**
     * Prefetch hot private identities only; missing keys never acquire refill locks.
     * @param array<int, string> $keys
     * @return array<string, array{found: bool, value: mixed}|null>
     */
    public function getPrivateMany(int $subjectId, array $keys): array
    {
        $entries = [];
        foreach (array_unique($keys) as $key) {
            $entries[$key] = [
                'identity' => RuntimeConfigIdentity::subjectPrivate($subjectId, $key),
                'mode' => SubjectConfigDefinitions::applyModeFor($key),
            ];
        }
        return $this->cache->getMany($entries);
    }

    /** @return ?array{config: array{found: bool, value: mixed}, companion: array} */
    public function getPrivateWithArray(int $subjectId, string $key, object $companion, string $companionKey): ?array
    {
        return $this->cache->getWithArray(RuntimeConfigIdentity::subjectPrivate($subjectId, $key),
            SubjectConfigDefinitions::applyModeFor($key), $companion, $companionKey);
    }

    /** @return array{shared_forget_succeeded: bool, warnings: array<int, string>} */
    public function invalidate(int $subjectId, string $key): array
    {
        $mode = SubjectConfigDefinitions::applyModeFor($key);
        $private = SubjectConfig::query()
            ->where('subject_id', $subjectId)
            ->where('key', $key)
            ->orderBy('id')
            ->first();
        $public = SubjectConfig::query()
            ->where('subject_id', $subjectId)
            ->whereIn('type', ['public', 'front'])
            ->where('key', $key)
            ->orderBy('id')
            ->first();

        $this->invalidator->replaceAfterCommit(
            RuntimeConfigIdentity::subjectPrivate($subjectId, $key),
            $mode,
            ['found' => $private !== null, 'value' => $private?->value]
        );
        $this->invalidator->replaceAfterCommit(
            RuntimeConfigIdentity::subjectPublic($subjectId, $key),
            $mode,
            ['found' => $public !== null, 'value' => $public?->value]
        );

        return [
            'shared_forget_succeeded' => true,
            'warnings' => [],
        ];
    }
}
