<?php

namespace Mallto\Admin\Domain\SubjectConfig;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/** Opt-in string enums share validation across the module form and legacy model saves. */
final class SubjectConfigEnumSchema
{
    /** @return array<string, string>|null Null means this definition has no enum schema. */
    public static function options(array $definition): ?array
    {
        if (!array_key_exists('enum_options', $definition)) {
            return null;
        }

        $options = $definition['enum_options'];
        if (!is_array($options) || $options === []) {
            throw new \InvalidArgumentException('Subject config enum_options must be a non-empty value-to-label map');
        }
        foreach ($options as $value => $label) {
            if ((string)$value === '' || trim((string)$value) !== (string)$value || !is_string($label)) {
                throw new \InvalidArgumentException('Subject config enum values must be non-empty strings with string labels');
            }
        }

        return $options;
    }

    /** Empty and null values remain inheritance; no value is coerced into an enum option. */
    public static function validate(array $definition, mixed $value): string
    {
        $options = self::options($definition);
        if ($options === null) {
            throw new \InvalidArgumentException('Subject config enum validation requires enum_options');
        }

        $name = (string)($definition['name'] ?? $definition['key'] ?? '配置项');
        $normalized = is_string($value) ? trim($value) : $value;
        $validated = Validator::make(['value' => $normalized], [
            'value' => ['nullable', 'string', Rule::in(array_map('strval', array_keys($options)))],
        ], [
            'value.string' => $name . '必须选择有效选项或留空继承。',
            'value.in' => $name . '必须选择有效选项或留空继承。',
        ])->validate();

        return $validated['value'] ?? '';
    }
}
