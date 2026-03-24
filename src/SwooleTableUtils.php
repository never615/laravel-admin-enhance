<?php
/**
 * Copyright (c) 2017. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin;

/**
 * Swoole\Table 通用读写工具类
 *
 * 封装 get / set / del / delByPrefix 操作，统一处理：
 *   - key 超长保护（Swoole\Table 默认 key 上限 64 字节）
 *   - value 超出列大小保护
 *   - TTL 过期检查
 *   - app('swoole') 不可用时的降级（静默返回）
 */
class SwooleTableUtils
{
    /**
     * Swoole\Table 默认 key 长度上限（字节）。
     * 超出此限制调用 set() 会抛出 "key is too long" 异常，需回退到 Redis 缓存。
     */
    public const MAX_KEY_LENGTH = 64;

    /**
     * 从指定 swoole_table 获取缓存值。
     *
     * @param string $table swoole_table 名称（对应 config/laravels.php swoole_tables 的 key）
     * @param string $key   缓存 key
     *
     * @return mixed|null 未命中或 key 超长时返回 null
     */
    public static function get(string $table, string $key)
    {
        // key 超出 Swoole\Table 限制时直接回退，避免 "key is too long" 异常
        if (strlen($key) > self::MAX_KEY_LENGTH) return null;

        try {
            $swooleTable = app('swoole')->{$table . 'Table'} ?? null;
        } catch (\Throwable $e) {
            return null;
        }
        if (!$swooleTable) return null;

        $row = $swooleTable->get($key);
        if ($row === false) return null;

        $expire = $row['expire'] ?? 0;
        if ($expire > 0 && $expire < time()) {
            $swooleTable->del($key);
            return null;
        }

        $value = $row['value'] ?? null;
        if (is_null($value) || $value === '') return null;

        return unserialize($value);
    }

    /**
     * 向指定 swoole_table 写入缓存值。
     *
     * key 超长或 value 序列化后超出列大小时静默跳过，
     * 由调用方的 Redis 层承担缓存职责。
     *
     * @param string $table swoole_table 名称
     * @param string $key   缓存 key
     * @param mixed  $value 要缓存的值（将被 serialize）
     * @param int    $ttl   TTL 秒数，0 表示不过期
     */
    public static function set(string $table, string $key, $value, int $ttl = 3600): void
    {
        // key 超出 Swoole\Table 限制时跳过，避免 "key is too long" 异常
        if (strlen($key) > self::MAX_KEY_LENGTH) return;

        try {
            $swooleTable = app('swoole')->{$table . 'Table'} ?? null;
        } catch (\Throwable $e) {
            return;
        }
        if (!$swooleTable) return;

        $serialized = serialize($value);
        // 超出列大小限制则跳过（从配置读取列大小，Swoole\Table::getColumns() 不存在）
        $columns = config('laravels.swoole_tables.' . $table . '.column', []);
        foreach ($columns as $col) {
            if (($col['name'] ?? '') === 'value' && strlen($serialized) > ($col['size'] ?? 2048)) return;
        }

        $swooleTable->set($key, [
            'value'  => $serialized,
            'expire' => $ttl > 0 ? time() + $ttl : 0,
        ]);
    }

    /**
     * 删除指定 swoole_table 中的缓存 key。
     *
     * @param string $table swoole_table 名称
     * @param string $key   缓存 key
     */
    public static function del(string $table, string $key): void
    {
        try {
            $swooleTable = app('swoole')->{$table . 'Table'} ?? null;
        } catch (\Throwable $e) {
            return;
        }
        if (!$swooleTable) return;

        $swooleTable->del($key);
    }

    /**
     * 按前缀删除 swoole_table 中的缓存 key。
     *
     * 遍历整张表，删除所有以 $prefix 开头的 key。
     *
     * @param string $table  swoole_table 名称
     * @param string $prefix key 前缀
     */
    public static function delByPrefix(string $table, string $prefix): void
    {
        try {
            $swooleTable = app('swoole')->{$table . 'Table'} ?? null;
        } catch (\Throwable $e) {
            return;
        }
        if (!$swooleTable || $prefix === '') return;

        foreach ($swooleTable as $key => $row) {
            if (strpos($key, $prefix) === 0) {
                $swooleTable->del($key);
            }
        }
    }
}

