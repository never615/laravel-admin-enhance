<?php

namespace Mallto\Admin\Domain\SubjectConfig;

class SubjectConfigDefinitions
{
    public static function modules(): array
    {
        return [
            'basic_front' => [
                'title' => '基础与前端',
                'description' => '短信、帮助文档、前端统计、前端接口域名等项目级配置。',
            ],
            'location_switch' => [
                'title' => '定位开关',
                'description' => '定位计算、静止判断、原始数据入库、基站更新等开关。',
            ],
            'location_algorithm' => [
                'title' => '定位算法',
                'description' => '定位算法参数、坐标转换、RSSI、离线和静止判断参数。',
            ],
            'map_style' => [
                'title' => '地图样式',
                'description' => '地图图层、颜色、文字大小、图标大小和精灵图。',
            ],
            'navigation' => [
                'title' => '导航',
                'description' => '导航权重、无障碍路径、导航距离和语音播报配置。',
            ],
            'push' => [
                'title' => '推送',
                'description' => 'HTTP/MQTT 定位结果、报警、基站和电量推送配置。',
            ],
            'debug' => [
                'title' => '日志排查',
                'description' => '项目级定位、围栏、区域报警、HTTP 推送等调试日志。',
            ],
            'vendor' => [
                'title' => '厂商接入',
                'description' => '海能达、利尔达等厂商接入参数。',
            ],
            'maintenance' => [
                'title' => '运行维护',
                'description' => '区域日志、报警日志、低电量阈值等维护类配置。',
            ],
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
        ];
    }

    public static function definitions(): array
    {
        $definitions = self::keyByConfigKey(array_merge(
            self::basicFrontDefinitions(),
            self::locationSwitchDefinitions(),
            self::locationAlgorithmDefinitions(),
            self::mapStyleDefinitions(),
            self::navigationDefinitions(),
            self::pushDefinitions(),
            self::debugDefinitions(),
            self::vendorDefinitions(),
            self::maintenanceDefinitions()
        ));

        foreach ((array)config('subject-config.subject_config_key', []) as $key => $name) {
            $key = (string)$key;
            if ($key === '' || isset($definitions[$key])) {
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

    public static function encodedKey(string $key): string
    {
        return rtrim(strtr(base64_encode($key), '+/', '-_'), '=');
    }

    private static function basicFrontDefinitions(): array
    {
        return [
            self::definition('sms_sign', '短信签名', 'basic_front', '', 'string', '项目短信签名；不配置时业务代码使用默认签名。'),
            self::definition('send_sms_code', '短信验证码发送开关', 'basic_front', '1', 'boolean', '关闭后项目短信验证码不发送。'),
            self::definition('wiki', '帮助文档地址', 'basic_front', '', 'string', '管理端底部帮助文档链接。'),
            self::definition('cdn_backend_domain', '后端接口 CDN 域名', 'basic_front', '', 'string', '前端初始化接口返回的后端接口 CDN 加速域名。'),
            self::definition('statistics_pid', '阿里云前端统计 PID', 'basic_front', '', 'string', '前端统计服务使用的 project id。'),
            self::definition('statistics_project', '阿里云日志项目', 'basic_front', 'web_log', 'string', '阿里云日志上报 project。'),
            self::definition('work_place_no_head_name', '用户作业头部名称', 'basic_front', '', 'string', '用户作业到位页面头部显示名称。'),
        ];
    }

    private static function locationSwitchDefinitions(): array
    {
        return [
            self::definition('location_calculate', '定位计算开关', 'location_switch', '1', 'boolean', '关闭后网关上报只进入后续更新逻辑，不执行定位计算。'),
            self::definition('location_move', '静止判断开关', 'location_switch', '0', 'boolean', '控制定位计算后是否启用静止判断。'),
            self::definition('location_debug', '定位原始数据入库', 'location_switch', '0', 'boolean', '控制定位原始数据是否写入 upload_beacons/sample_beacons 等调试数据表。'),
            self::definition('cache_beacon', 'Cache Beacon 入库开关', 'location_switch', '0', 'boolean', '控制是否写入 cache_beacon 表。'),
            self::definition('location_notify_enable', '定位器信息变化通知', 'location_switch', '1', 'boolean', '定位器电量、心跳、遮挡等信息变化时是否触发通知任务。'),
            self::definition('upload_locator_station_update', '定位器扫描基站更新', 'location_switch', '0', 'boolean', '定位器或手机扫描到基站后，是否允许更新 beacons 表中的基站信息。'),
            self::definition('upload_other_station_update', '其他设备扫描基站更新', 'location_switch', '0', 'boolean', '网关解析到未识别设备时，是否在限流窗口内投递基站更新任务。'),
            self::definition('floor_location_args_support', '楼层定位参数覆盖', 'location_switch', '0', 'boolean', '开启后定位算法参数允许叠加楼层级配置。'),
        ];
    }

    private static function locationAlgorithmDefinitions(): array
    {
        return [
            self::definition('default_positioning_args', '默认定位算法参数', 'location_algorithm', '', 'json', 'JSON 对象；会覆盖代码内置默认定位参数。'),
            self::definition('active_positioning_args', '主动定位算法参数', 'location_algorithm', '', 'json', 'JSON 对象；主动定位模式下覆盖默认参数。'),
            self::definition('passive_positioning_args', '被动定位算法参数', 'location_algorithm', '', 'json', 'JSON 对象；被动定位模式下覆盖默认参数。'),
            self::definition('mobile_positioning_args', '手机定位算法参数', 'location_algorithm', '', 'json', 'JSON 对象；手机定位模式下覆盖默认参数。'),
            self::definition('coor_mapping_switch', '坐标转换开关', 'location_algorithm', '0', 'boolean', '开启后按 coor_mapping_args 执行第三方坐标到系统坐标转换。'),
            self::definition('coor_mapping_args', '坐标转换参数', 'location_algorithm', '', 'json', 'JSON 对象；第三方系统坐标到墨兔系统坐标的转换参数。'),
            self::definition('tx_rssi', '默认 1m RSSI', 'location_algorithm', '', 'integer', '默认定位 1m RSSI。留空时使用 config(location.tx_rssi)。'),
            self::definition('passive_tx_rssi', '被动定位 1m RSSI', 'location_algorithm', '', 'integer', '被动定位模式专用 1m RSSI。'),
            self::definition('active_tx_rssi', '主动定位 1m RSSI', 'location_algorithm', '', 'integer', '主动定位模式专用 1m RSSI。'),
            self::definition('mobile_tx_rssi', '手机定位 1m RSSI', 'location_algorithm', '', 'integer', '手机定位模式专用 1m RSSI。'),
            self::definition('move_check_minutes', '静止判断分钟数', 'location_algorithm', '5', 'integer', '定位器超过该分钟数未发生位置更新时标记为静止。'),
            self::definition('offline_minutes', '离线判断分钟数', 'location_algorithm', '', 'integer', '定位器离线检查使用；留空时使用定位参数中的 locator_cache_time。'),
            self::definition('upload_beacons_database', 'Upload Beacon 数据库', 'location_algorithm', 'pgsql', 'string', '历史调试数据保存数据库连接名。当前代码中只保留兼容配置。'),
        ];
    }

    private static function mapStyleDefinitions(): array
    {
        return [
            self::definition('sprite', '地图精灵图地址', 'map_style', 'sprite', 'string', 'Mapbox style sprite 地址或标识。'),
            self::definition('map_layer', '项目默认地图图层', 'map_style', '', 'json', '完整 Mapbox layers JSON 数组；配置后覆盖默认图层生成逻辑。'),
            self::definition('default_base_bg_color', '默认底图背景色', 'map_style', '#fafafa', 'string', '地图 block 未命中时使用的默认底色，例如 #fafafa。'),
            self::definition('compartment_color', '隔间颜色阶梯', 'map_style', '', 'json', 'Mapbox step 表达式后半段数组，例如 ["#027a00",60,"#0eed30"]。'),
            self::definition('place_style_text_size', 'Place 文字大小', 'map_style', '10', 'integer', '地图 place 名称文字大小，单位 px。'),
            self::definition('facilities_style_icon_size', '设施图标大小', 'map_style', '0.6', 'float', '地图设施图标缩放比例。'),
            self::definition('bearing', '地图默认旋转角度', 'map_style', '0', 'float', '历史地图接口兼容配置。'),
        ];
    }

    private static function navigationDefinitions(): array
    {
        return [
            self::definition('navigation_config', '导航参数配置', 'navigation', '', 'json', '普通路线权重 JSON：ladder_start、ladder_per、esc_per、stair_per、avoid_park_area_threshold。'),
            self::definition('navigation_config_road1', '无障碍导航参数配置', 'navigation', '', 'json', '无障碍路线权重 JSON，字段同 navigation_config。'),
            self::definition('navigation_project_distance', '导航项目距离限制', 'navigation', '', 'integer', '导航服务按项目可覆盖的距离阈值。'),
            self::definition('navigation_voice_copywriter_config', '导航语音文案开关', 'navigation', '0', 'boolean', '开启后使用带楼层信息的导航语音文案模板。'),
        ];
    }

    private static function pushDefinitions(): array
    {
        return [
            self::definition('mqtt_publish_enable', 'MQTT 发布开关', 'push', '0', 'boolean', '项目级 MQTT 结果发布开关；仍受全局 MQTT 开关限制。'),
            self::definition('location_push_event', '旧定位推送开关', 'push', '0', 'boolean', '历史定位推送开关，建议优先使用 http_location_push_event / mqtt_location_push_event。'),
            self::definition('http_location_push_event', 'HTTP 定位结果推送', 'push', '0', 'boolean', '开启后定位结果允许走 HTTP 推送。'),
            self::definition('mqtt_location_push_event', 'MQTT 定位结果推送', 'push', '0', 'boolean', '开启后定位结果允许走 MQTT 推送。'),
            self::definition('http_beacon_push_event', 'HTTP Beacon 推送', 'push', '0', 'boolean', '开启后 beacon 相关事件允许走 HTTP 推送。'),
            self::definition('third_party_location_push_domain', '第三方定位接收地址', 'push', '', 'string', '旧定位推送接收地址。'),
            self::definition('third_part_warning_push_url', '第三方报警推送地址', 'push', '', 'string', '默认报警推送地址；专项 URL 未配置时使用。'),
            self::definition('third_part_location_result_push_url', '定位结果专项推送地址', 'push', '', 'string', '定位结果专项 HTTP 推送地址。'),
            self::definition('third_part_fence_push_url', '围栏报警专项推送地址', 'push', '', 'string', '电子围栏报警专项 HTTP 推送地址。'),
            self::definition('third_part_area_alarm_push_url', '区域报警专项推送地址', 'push', '', 'string', '区域报警专项 HTTP 推送地址。'),
            self::definition('third_part_location_area_push_url', '区域位置专项推送地址', 'push', '', 'string', '区域位置变化专项 HTTP 推送地址。'),
            self::definition('third_part_beacon_push_url', 'Beacon 专项推送地址', 'push', '', 'string', 'Beacon 事件专项 HTTP 推送地址。'),
            self::definition('third_part_battery_warning_push_url', '低电量专项推送地址', 'push', '', 'string', '低电量报警专项 HTTP 推送地址。'),
            self::definition('third_part_gateway_online_push_url', '网关在线专项推送地址', 'push', '', 'string', '网关在线状态专项 HTTP 推送地址。'),
            self::definition('third_part_station_online_push_url', '基站在线专项推送地址', 'push', '', 'string', '基站在线状态专项 HTTP 推送地址。'),
            self::definition('third_party_fence_push_domain', '化工厂围栏推送域名', 'push', 'http://61.190.204.162:60010', 'string', '化工厂围栏报警推送域名，代码会追加固定 path。'),
            self::definition('location_notify_token', '化工厂定位推送 Token', 'push', '123BCB62C38ABFE1', 'string', '化工厂定位推送 token。'),
            self::definition('location_notify_qyid', '化工厂定位推送企业 ID', 'push', 'c6863cc6bf06452396d057047b59fe84', 'string', '化工厂定位推送 qyid。'),
            self::definition('http_push_verify_ssl', 'HTTP 推送 SSL 校验', 'push', '0', 'boolean', '历史 HTTP 推送 SSL 校验开关。'),
            self::definition('http_push_ca_path', 'HTTP 推送 CA 路径', 'push', '', 'string', '历史 HTTP 推送自定义 CA 文件路径。'),
        ];
    }

    private static function debugDefinitions(): array
    {
        return [
            self::definition('location_fence_debug', '围栏调试日志', 'debug', '0', 'boolean', '围栏相关调试日志。'),
            self::definition('location_fence_log', '围栏命中日志', 'debug', '0', 'boolean', '定位结果进入围栏判断时的命中日志。'),
            self::definition('location_area_alarm_log', '区域报警调试日志', 'debug', '0', 'boolean', '区域报警判断过程日志。'),
            self::definition('acapi_debug', '海能达 ACAPI 调试日志', 'debug', '0', 'boolean', '海能达网关 ACAPI 数据处理调试日志。'),
            self::definition('http_push_error_log', 'HTTP 推送异常日志', 'debug', '0', 'boolean', 'HTTP 推送异常时打印错误上下文。'),
        ];
    }

    private static function vendorDefinitions(): array
    {
        return [
            self::definition('hytera_connect_ip', '海能达连接 IP', 'vendor', '172.31.1.1', 'string', '海能达 ACAPI 连接地址。', self::familyMeta('hytera')),
            self::definition('hytera_connect_port', '海能达连接端口', 'vendor', '4300', 'integer', '海能达 ACAPI 连接端口。', self::familyMeta('hytera')),
            self::definition('hytera_login_name', '海能达登录名', 'vendor', 'hytera', 'string', '海能达 ACAPI 登录名。', self::familyMeta('hytera')),
            self::definition('hytera_password', '海能达密码', 'vendor', 'hytera', 'string', '海能达 ACAPI 密码。', self::familyMeta('hytera')),
            self::definition('hytera_api_verseion', '海能达 API 版本', 'vendor', '36', 'integer', '海能达 ACAPI 版本号。', self::familyMeta('hytera')),
            self::definition('hytera_servicereq', '海能达服务类型', 'vendor', '1', 'integer', '1: Short Data Service；2: Monitoring Services。', self::familyMeta('hytera')),
            self::definition('hytera_ssiidentity', '海能达 SSI Identity', 'vendor', '304', 'integer', '海能达 ACAPI SSI Identity。', self::familyMeta('hytera')),
            self::definition('lierda_http_sign_token', '利尔达 HTTP 签名 Token', 'vendor', '', 'string', '利尔达 HTTP 回调验签 token。', self::familyMeta('lierda')),
            self::definition('lierda_project_sign_token', '利尔达项目签名 Token', 'vendor', '', 'string', '利尔达项目级验签 token。', self::familyMeta('lierda')),
        ];
    }

    private static function maintenanceDefinitions(): array
    {
        return [
            self::definition('area_log_store', '区域日志入库开关', 'maintenance', '0', 'boolean', '开启后定位结果触发区域日志写入。'),
            self::definition('area_log_push', '区域日志异步推送开关', 'maintenance', '0', 'boolean', '开启后区域日志通过异步任务写入。'),
            self::definition('area_alarm_silent_time', '区域报警静默分钟数', 'maintenance', '1440', 'integer', '同一定位器同一区域报警的静默时间，单位分钟。'),
            self::definition('location_area_changed_ttl', '区域变化缓存秒数', 'maintenance', '10', 'integer', '区域发生变化时的缓存有效时间，单位秒。'),
            self::definition('location_area_deleted_ttl', '区域删除缓存秒数', 'maintenance', '10', 'integer', '区域删除时的缓存有效时间，单位秒。'),
            self::definition('locator_low_battery_config', '定位器低电量阈值', 'maintenance', '20', 'integer', '定位器电量小于等于该值时触发低电量报警。'),
            self::definition('fence_area_alarm_logs_max_count', '报警日志最大条数', 'maintenance', '', 'integer', '当前项目围栏/区域报警日志最多保留条数；小于等于 0 表示不按条数清理。'),
            self::definition('fence_area_alarm_logs_max_months', '报警日志最大月数', 'maintenance', '', 'integer', '当前项目围栏/区域报警日志最多保留月数；小于等于 0 表示不按时间清理。'),
        ];
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
        ], $meta);
    }

    private static function familyMeta(string $familyKey): array
    {
        $labels = [
            'hytera' => '海能达',
            'lierda' => '利尔达',
        ];

        return [
            'family_key' => $familyKey,
            'family_label' => $labels[$familyKey] ?? $familyKey,
        ];
    }

    private static function keyByConfigKey(array $definitions): array
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

    private static function sortValue(array $definition): int
    {
        $orders = array_flip(array_keys(self::modules()));
        $module = (string)($definition['module'] ?? 'history');

        return (($orders[$module] ?? 999) + 1) * 1000;
    }
}
