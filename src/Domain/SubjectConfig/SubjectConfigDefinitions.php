<?php

namespace Mallto\Admin\Domain\SubjectConfig;

class SubjectConfigDefinitions
{
    public static function modules(): array
    {
        return array_merge(SubjectConfigRegistry::modules(), [
            'legacy' => [
                'title' => '旧预设配置',
                'description' => '旧 subject-config.php 中仍保留的预设 key。',
            ],
            'history' => [
                'title' => '历史/未归类',
                'description' => '数据库中已经存在，但代码侧尚未整理成固定定义的 key。',
            ],
            'temporary_station' => [
                'title' => '临时基站',
                'description' => 'sa_lo_st_ 前缀的临时定位基站保存开关。',
            ],
        ]);
    }

    public static function definitions(): array
    {
        $definitions = SubjectConfigDefinition::keyByConfigKey(SubjectConfigRegistry::definitions());

        foreach ((array)config('subject-config.subject_config_key', []) as $key => $name) {
            $key = (string)$key;
            if ($key === '' || isset($definitions[$key]) || SubjectConfigRegistry::isRetired($key)) {
                continue;
            }

            $definitions[$key] = self::definition(
                $key,
                (string)$name,
                'legacy',
                '',
                'string',
                '旧预设动态配置。请优先确认业务代码是否仍在读取该 key。'
            );
        }

        uasort($definitions, function (array $left, array $right) {
            return self::sortValue($left) <=> self::sortValue($right);
        });

        return $definitions;
    }

    public static function module(string $module): ?array
    {
        $modules = self::modules();

        return $modules[$module] ?? null;
    }

    public static function moduleLabel(string $module): string
    {
        return (string)(self::module($module)['title'] ?? $module);
    }

    public static function applyModeFor(string $key): string
    {
        $definition = SubjectConfigRegistry::definition($key);

        return SubjectConfigApplyMode::normalize(
            is_array($definition) ? ($definition['apply_mode'] ?? null) : null
        );
    }

    public static function isLiveCached(string $key): bool
    {
        return self::applyModeFor($key) === SubjectConfigApplyMode::SHARED_REDIS;
    }

    public static function applyModeLabelFor(string $key): string
    {
        return SubjectConfigApplyMode::label(self::applyModeFor($key));
    }

    public static function encodedKey(string $key): string
    {
        return rtrim(strtr(base64_encode($key), '+/', '-_'), '=');
    }

    private static function definition(
        string $key,
        string $name,
        string $module,
        string $defaultValue,
        string $type,
        string $remark,
        array $meta = []
    ): array {
        return SubjectConfigDefinition::make($key, $name, $module, $defaultValue, $type, $remark, $meta);
    }

    private static function sortValue(array $definition): int
    {
        $orders = array_flip(array_keys(self::modules()));
        $module = (string)($definition['module'] ?? 'history');

        return (($orders[$module] ?? 999) + 1) * 1000;
    }
}
