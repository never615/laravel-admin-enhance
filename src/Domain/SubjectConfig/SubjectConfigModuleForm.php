<?php

namespace Mallto\Admin\Domain\SubjectConfig;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Data\SubjectConfig;
use Mallto\Admin\SubjectUtils;
use Mallto\Tool\Domain\NewConfig\NewConfigPublisher;
use Mallto\Tool\Exception\ResourceException;

class SubjectConfigModuleForm
{
    private const TEMPORARY_STATION_PREFIX = 'sa_lo_st_';

    public function __construct(private NewConfigPublisher $publisher)
    {
    }

    public function snapshot(?int $subjectId = null): array
    {
        $definitions = SubjectConfigDefinitions::definitions();
        $rows = collect();
        $subject = null;

        if ($subjectId) {
            $subject = Subject::query()->find($subjectId);
            if (!$subject) {
                throw new ResourceException('项目不存在或无权限访问。');
            }

            $rows = SubjectConfig::query()
                ->where('subject_id', $subjectId)
                ->orderBy('key')
                ->get()
                ->keyBy('key');
        }

        $fixedRows = collect($definitions)
            ->map(function (array $definition, string $key) use ($rows) {
                return $this->rowSnapshot($key, $definition, $rows->get($key), false);
            })
            ->values()
            ->all();

        $historyRows = $rows
            ->filter(function (SubjectConfig $row, string $key) use ($definitions) {
                return !isset($definitions[$key]) && !str_starts_with($key, self::TEMPORARY_STATION_PREFIX);
            })
            ->map(function (SubjectConfig $row, string $key) {
                return $this->rowSnapshot($key, [
                    'key' => $key,
                    'name' => $key,
                    'module' => 'history',
                    'type' => 'string',
                    'default_value' => '',
                    'remark' => '数据库中已经存在的历史动态配置。表单仅允许修改 value，不提供任意新增。',
                    'ui' => 'textarea',
                ], $row, true);
            })
            ->values()
            ->all();

        $temporaryStationRows = $rows
            ->filter(fn(SubjectConfig $row, string $key) => str_starts_with($key, self::TEMPORARY_STATION_PREFIX))
            ->map(fn(SubjectConfig $row) => [
                'key' => (string)$row->key,
                'value' => (string)$row->value,
                'type' => (string)($row->type ?: 'private'),
                'remark' => (string)($row->remark ?: ''),
            ])
            ->values()
            ->all();

        return [
            'title' => '项目动态配置',
            'description' => '配置中心 / 项目动态配置',
            'subject_id' => $subjectId,
            'subject' => $subject,
            'subjects' => $this->subjectOptions(),
            'modules' => SubjectConfigDefinitions::modules(),
            'rows' => $fixedRows,
            'history_rows' => $historyRows,
            'temporary_station_rows' => $temporaryStationRows,
            'temporary_station_limit' => max(1, (int)config('subject_config_runtime.sa_lo_st_limit', 10)),
        ];
    }

    public function save(array $input): array
    {
        $subjectId = (int)($input['subject_id'] ?? 0);
        if ($subjectId <= 0) {
            throw new ResourceException('请先选择项目。');
        }

        if (!Subject::query()->whereKey($subjectId)->exists()) {
            throw new ResourceException('项目不存在或无权限访问。');
        }

        $definitions = SubjectConfigDefinitions::definitions();
        $values = (array)($input['values'] ?? []);
        $historyValues = (array)($input['history'] ?? []);
        $stationRows = (array)($input['temporary_station'] ?? []);

        $existingRows = SubjectConfig::query()
            ->where('subject_id', $subjectId)
            ->get()
            ->keyBy('key');

        foreach ($definitions as $key => $definition) {
            $encodedKey = SubjectConfigDefinitions::encodedKey($key);
            $existing = $existingRows->get($key);
            $submittedValue = array_key_exists($encodedKey, $values)
                ? $values[$encodedKey]
                : ($existing ? $existing->value : ($definition['default_value'] ?? ''));
            $value = $this->validateValue($key, $definition, $submittedValue);
            $normalized = $this->normalizeValue($definition, $value);

            if (!$this->shouldPersistFixedValue($definition, $normalized, $existing)) {
                continue;
            }

            $this->saveValue($subjectId, $key, $definition, $normalized, $existing);
        }

        foreach ($historyValues as $encodedKey => $value) {
            $key = $this->decodeKey((string)$encodedKey);
            if ($key === '' || isset($definitions[$key]) || str_starts_with($key, self::TEMPORARY_STATION_PREFIX)) {
                continue;
            }

            $existing = $existingRows->get($key);
            if (!$existing) {
                continue;
            }

            $this->saveValue($subjectId, $key, [
                'type' => 'string',
                'subject_config_type' => $existing->type ?: 'private',
                'remark' => $existing->remark ?: '',
            ], trim((string)$value), $existing);
        }

        $this->saveTemporaryStationRows($subjectId, $stationRows, $existingRows);

        $this->publisher->publish(false);

        return $this->snapshot($subjectId);
    }

    private function saveTemporaryStationRows(int $subjectId, array $stationRows, $existingRows): void
    {
        foreach ($stationRows as $row) {
            if (!is_array($row)) {
                continue;
            }

            $key = trim((string)($row['key'] ?? ''));
            $value = trim((string)($row['value'] ?? ''));
            $remark = trim((string)($row['remark'] ?? ''));

            if ($key === '') {
                continue;
            }

            if (!str_starts_with($key, self::TEMPORARY_STATION_PREFIX)) {
                throw new ResourceException('临时基站 key 必须以 ' . self::TEMPORARY_STATION_PREFIX . ' 开头。');
            }

            $saved = $this->saveValue($subjectId, $key, [
                'type' => 'string',
                'subject_config_type' => 'private',
                'remark' => $remark,
            ], $value, $existingRows->get($key));
            $existingRows->put($key, $saved);
        }
    }

    private function saveValue(
        int $subjectId,
        string $key,
        array $definition,
        string $value,
        ?SubjectConfig $existing
    ): SubjectConfig {
        $config = $existing ?: new SubjectConfig();
        $config->subject_id = $subjectId;
        $config->key = $key;
        $config->value = $value;
        $config->type = $existing && $existing->type
            ? $existing->type
            : (string)($definition['subject_config_type'] ?? 'private');

        if (!$existing || !$existing->remark) {
            $config->remark = (string)($definition['remark'] ?? '');
        }

        $config->save();
        SubjectUtils::clearDynamicConfig($key, $subjectId);

        return $config;
    }

    private function shouldPersistFixedValue(array $definition, string $value, ?SubjectConfig $existing): bool
    {
        if ($existing) {
            return true;
        }

        $default = $this->normalizeValue($definition, (string)($definition['default_value'] ?? ''));

        return $value !== '' && $value !== $default;
    }

    private function rowSnapshot(string $key, array $definition, ?SubjectConfig $row, bool $history): array
    {
        $value = $row ? $row->value : null;
        $source = '默认值';
        if ($value === null || $value === '') {
            $value = $definition['default_value'] ?? '';
        } else {
            $source = '已配置';
        }

        $module = (string)($definition['module'] ?? 'history');

        return [
            'key' => $key,
            'encoded_key' => SubjectConfigDefinitions::encodedKey($key),
            'name' => (string)($definition['name'] ?? $key),
            'value' => (string)$value,
            'type' => (string)($definition['type'] ?? 'string'),
            'ui' => (string)($definition['ui'] ?? 'input'),
            'placeholder' => (string)($definition['placeholder'] ?? ''),
            'module' => $module,
            'module_label' => SubjectConfigDefinitions::moduleLabel($module),
            'family_key' => (string)($definition['family_key'] ?? ''),
            'family_label' => (string)($definition['family_label'] ?? ''),
            'default_value' => (string)($definition['default_value'] ?? ''),
            'remark' => (string)($definition['remark'] ?? ($row->remark ?? '')),
            'subject_config_type' => $row && $row->type
                ? (string)$row->type
                : (string)($definition['subject_config_type'] ?? 'private'),
            'source' => $source,
            'history' => $history,
        ];
    }

    private function validateValue(string $key, array $definition, $value)
    {
        $name = (string)($definition['name'] ?? $key);
        $type = (string)($definition['type'] ?? 'string');

        $rules = match ($type) {
            'boolean' => ['required', Rule::in(['0', '1', 0, 1, true, false])],
            'integer' => ['nullable', 'integer'],
            'float' => ['nullable', 'numeric'],
            'json' => ['nullable', 'json'],
            default => ['nullable', 'string'],
        };

        $messages = [
            'value.required' => $name . '不能为空。',
            'value.in' => $name . '只能选择开启或关闭。',
            'value.integer' => $name . '必须是整数。',
            'value.numeric' => $name . '必须是数字。',
            'value.json' => $name . '必须是合法 JSON。',
        ];

        return Validator::make(['value' => $value], ['value' => $rules], $messages)
            ->validate()['value'] ?? '';
    }

    private function normalizeValue(array $definition, $value): string
    {
        return match ((string)($definition['type'] ?? 'string')) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN) ? '1' : '0',
            'integer' => $value === '' || $value === null ? '' : (string)((int)$value),
            'float' => $value === '' || $value === null ? '' : (string)((float)$value),
            default => trim((string)$value),
        };
    }

    private function decodeKey(string $encodedKey): string
    {
        $padding = strlen($encodedKey) % 4;
        if ($padding) {
            $encodedKey .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode(strtr($encodedKey, '-_', '+/'), true);

        return $decoded === false ? '' : $decoded;
    }

    private function subjectOptions(): array
    {
        if (!Schema::hasTable('subjects')) {
            return [];
        }

        try {
            $query = Subject::dynamicData();
        } catch (\Throwable) {
            $query = Subject::query()->orderBy('id', 'desc');
        }

        return $query
            ->limit(500)
            ->pluck('name', 'id')
            ->map(fn($name, $id) => (string)$name . ' (ID:' . $id . ')')
            ->toArray();
    }
}
