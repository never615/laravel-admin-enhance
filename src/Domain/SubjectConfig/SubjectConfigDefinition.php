<?php

namespace Mallto\Admin\Domain\SubjectConfig;

class SubjectConfigDefinition
{
    public static function make(
        string $key,
        string $name,
        string $module,
        string $defaultValue,
        string $type,
        string $remark,
        array $meta = []
    ): array {
        return array_merge([
            'key' => $key,
            'name' => $name,
            'module' => $module,
            'type' => $type,
            'default_value' => $defaultValue,
            'remark' => $remark,
            'subject_config_type' => 'private',
            'ui' => $type === 'json' ? 'textarea' : 'input',
            'placeholder' => '',
            'family_key' => '',
            'family_label' => '',
            'apply_mode' => SubjectConfigApplyMode::SWOOLE_REDIS,
        ], $meta);
    }

    public static function keyByConfigKey(array $definitions): array
    {
        $keyed = [];
        foreach ($definitions as $definition) {
            $key = (string)($definition['key'] ?? '');
            if ($key === '') {
                continue;
            }

            $keyed[$key] = $definition;
        }

        return $keyed;
    }
}
