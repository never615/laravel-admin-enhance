<?php

namespace Mallto\Admin\Domain\Permission;

use Mallto\Admin\Data\AdminApiPermission;

/**
 * Maps admin API routes to the operation permission used by the frontend role tree.
 */
class AdminApiPermissionResolver
{
    private $permissionExists;

    public function __construct(?callable $permissionExists = null)
    {
        $this->permissionExists = $permissionExists;
    }

    /**
     * Resolve a route name to an explicit permission slug.
     *
     * @param string|null $routeName
     * @return string|null
     */
    public function resolve(?string $routeName): ?string
    {
        if (!$routeName) {
            return null;
        }

        $specialRoutePermissions = [
            'admin_api.front_permissions.index' => 'admin_api.admin_role.index',
            'admin_api.nearest_locator.index' => 'admin_api.position',
            'admin_api.history_locator.index' => 'admin_api.historical_track',
            'admin_api.sync_map_data.index' => 'admin_api.sync_map_data.sync',
            'admin_api.locator_slug.getLocatorSlug' => 'admin_api.locator.create',
            'admin_api.wechat_unbound.store' => 'admin_api.wechat_user.destroy',
            'admin_api.work_place_report_record.destroy' => 'admin_api.work_place_report_record.index.destroy',
            'admin_api.beacon_inspection_records.index' => 'admin_api.beacon_inspection_record.index',
            'admin_api.beacon_inspection_records.store' => 'admin_api.beacon_inspection_record.create',
            'admin_api.beacon_inspection_records.show' => 'admin_api.beacon_inspection_record.index',
            'admin_api.work_place_report_record.index' => 'admin_api.work_place_report_record.index.index',
        ];
        if (isset($specialRoutePermissions[$routeName])) {
            $mappedPermission = $specialRoutePermissions[$routeName];
            if ($routeName === 'admin_api.work_place_report_record.index') {
                if ($this->permissionExists($mappedPermission)) {
                    return $mappedPermission;
                }

                return $this->permissionExists($routeName) ? $routeName : null;
            }

            return $mappedPermission;
        }

        $routeParts = explode('.', $routeName);
        if (count($routeParts) < 3) {
            return $this->permissionExists($routeName) ? $routeName : null;
        }

        $action = array_pop($routeParts);
        $resource = implode('.', $routeParts);
        $resource = [
            'admin_api.front_roles' => 'admin_api.admin_role',
            'admin_api.front_admin_users' => 'admin_api.admin_user',
            'admin_api.tags' => 'admin_api.tag',
            'admin_api.beacon' => 'admin_api.beacons',
            'admin_api.beacon_uuid' => 'admin_api.beacons',
        ][$resource] ?? $resource;

        if ($action === 'show' && $this->permissionExists($resource . '.show')) {
            return $resource . '.show';
        }

        if ($action === 'edit' && $this->permissionExists($resource . '.edit')) {
            return $resource . '.edit';
        }

        if (in_array($action, ['index', 'show', 'edit'], true)) {
            return $resource . '.index';
        }

        if (in_array($action, ['create', 'store', 'update'], true)) {
            if ($resource === 'admin_api.fence_config') {
                $resource = 'admin_api.fence';
            }

            return $resource . '.create';
        }

        if ($action === 'destroy') {
            return $resource . '.destroy';
        }

        if ($action === 'import') {
            return preg_replace('/_import$/', '', $resource) . '.create';
        }

        if ($action === 'export') {
            return preg_replace('/_export$/', '', $resource) . '.export';
        }

        return $this->permissionExists($routeName) ? $routeName : null;
    }

    /**
     * Resolve the export permission associated with a list route.
     */
    public function resolveExport(?string $permissionSlug): ?string
    {
        if (!$permissionSlug || !str_ends_with($permissionSlug, '.index')) {
            return null;
        }

        return substr($permissionSlug, 0, -strlen('.index')) . '.export';
    }

    private function permissionExists(string $slug): bool
    {
        if ($this->permissionExists) {
            return (bool)call_user_func($this->permissionExists, $slug);
        }

        return AdminApiPermission::query()->where('slug', $slug)->exists();
    }
}
