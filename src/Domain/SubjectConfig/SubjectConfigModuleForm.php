<?php

namespace Mallto\Admin\Domain\SubjectConfig;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Mallto\Admin\Data\Subject;
use Mallto\Admin\Data\SubjectConfig;
use Mallto\Tool\Domain\NewConfig\NewConfigPublisher;
use Mallto\Tool\Exception\ResourceException;

class SubjectConfigModuleForm
{
    private const TEMPORARY_STATION_PREFIX = 'sa_lo_st_';
    private const SYSTEM_MAINTENANCE_SUBJECT_ID = 1;

    public function __construct(private NewConfigPublisher $publisher)
    {
    }

    public function snapshot(?int $subjectId = null): array
    {
        $definitions = SubjectConfigDefinitions::definitions();
        $rows = collect();
        $subject = null;

        if ($subjectId) {
            $this->ensureSubjectCanBeConfigured($subjectId);
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
                'apply_mode' => SubjectConfigApplyMode::SNAPSHOT_RELOAD,
                'apply_mode_label' => SubjectConfigApplyMode::label(
                    SubjectConfigApplyMode::SNAPSHOT_RELOAD
                ),
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
        $this->ensureSubjectCanBeConfigured($subjectId);

        $definitions = SubjectConfigDefinitions::definitions();
        $values = (array)($input['values'] ?? []);
        $historyValues = (array)($input['history'] ?? []);
        $stationRows = (array)($input['temporary_station'] ?? []);

        $changedKeys = DB::transaction(function () use (
            $subjectId,
            $definitions,
            $values,
            $historyValues,
            $stationRows
        ): array {
            $subject = Subject::query()->whereKey($subjectId)->lockForUpdate()->first();
            if (!$subject) {
                throw new ResourceException('项目不存在或无权限访问。');
            }

            $existingRows = SubjectConfig::query()
                ->where('subject_id', $subjectId)
                ->get()
                ->keyBy('key');
            $changedKeys = [];

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

                $saved = $this->saveValue($subjectId, $key, $definition, $normalized, $existing);
                $existingRows->put($key, $saved);
                $changedKeys[] = $key;
            }

            foreach ($historyValues as $encodedKey => $value) {
                $key = $this->decodeKey((string)$encodedKey);
                if ($key === '' || isset($definitions[$key]) || str_starts_with($key, self::TEMPORARY_STATION_PREFIX)) {
                    continue;
                }

                $existing = $existingRows->get($key);
                $normalized = trim((string)$value);
                if (!$existing || (string)$existing->value === $normalized) {
                    continue;
                }

                $this->saveValue($subjectId, $key, [
                    'type' => 'string',
                    'subject_config_type' => $existing->type ?: 'private',
                    'remark' => $existing->remark ?: '',
                ], $normalized, $existing);
                $changedKeys[] = $key;
            }

            return array_merge(
                $changedKeys,
                $this->saveTemporaryStationRows($subjectId, $stationRows, $existingRows)
            );
        });

        $changedKeys = array_values(array_unique(array_map('strval', $changedKeys)));
        sort($changedKeys);
        $liveKeys = $changedKeys;
        $restartKeys = [];

        return [
            'snapshot' => $this->snapshot($subjectId),
            'apply' => [
                'changed_keys' => array_map(static fn(string $key): array => [
                    'key' => $key,
                    'apply_mode' => SubjectConfigDefinitions::applyModeFor($key),
                ], $changedKeys),
                'live_keys' => $liveKeys,
                'restart_keys' => $restartKeys,
                'requires_restart' => $restartKeys !== [],
                'published' => false,
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function saveTemporaryStationRows(int $subjectId, array $stationRows, $existingRows): array
    {
        $changedKeys = [];
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

            $existing = $existingRows->get($key);
            $valueChanged = !$existing || (string)$existing->value !== $value;
            $remarkChanged = $existing && (string)($existing->remark ?? '') !== $remark;
            if (!$valueChanged && !$remarkChanged) {
                continue;
            }

            if ($existing) {
                $existing->remark = $remark;
            }
            $saved = $this->saveValue($subjectId, $key, [
                'type' => 'string',
                'subject_config_type' => 'private',
                'remark' => $remark,
            ], $value, $existing);
            $existingRows->put($key, $saved);
            if ($valueChanged) {
                $changedKeys[] = $key;
            }
        }

        return $changedKeys;
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

        return $config;
    }

    private function shouldPersistFixedValue(array $definition, string $value, ?SubjectConfig $existing): bool
    {
        if ($existing) {
            return (string)$existing->value !== $value;
        }

        $default = $this->normalizeValue($definition, (string)($definition['default_value'] ?? ''));

        return $value !== $default;
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
            'apply_mode' => SubjectConfigDefinitions::applyModeFor($key),
            'apply_mode_label' => SubjectConfigDefinitions::applyModeLabelFor($key),
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

    private function ensureSubjectCanBeConfigured(int $subjectId): void
    {
        if ($subjectId === self::SYSTEM_MAINTENANCE_SUBJECT_ID) {
            throw new ResourceException('系统维护项目不可在项目动态配置中操作。');
        }
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
            ->whereKeyNot(self::SYSTEM_MAINTENANCE_SUBJECT_ID)
            ->limit(500)
            ->pluck('name', 'id')
            ->map(fn($name, $id) => (string)$name . ' (ID:' . $id . ')')
            ->toArray();
    }
}
