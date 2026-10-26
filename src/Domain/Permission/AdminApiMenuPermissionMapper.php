<?php

namespace Mallto\Admin\Domain\Permission;

/**
 * Maps assigned API permissions to frontend menu visibility and operation flags.
 */
class AdminApiMenuPermissionMapper
{
    /**
     * Return only menu URIs with an explicitly assigned read permission.
     *
     * @param string[] $assignedSlugs
     * @param array<string, bool> $allPermissionSlugs
     * @return string[]
     */
    public function visibleMenuUris(array $assignedSlugs, array $allPermissionSlugs): array
    {
        $visibleMenuUris = [];

        foreach ($assignedSlugs as $assignedSlug) {
            $slug = str_replace('admin_api.', '', $assignedSlug);

            if ($this->hasCrudChildren('admin_api.' . $slug, $allPermissionSlugs)) {
                $visibleMenuUris[] = $slug;
                continue;
            }

            if (str_ends_with($slug, '.index.index')) {
                $resourceSlug = 'admin_api.' . substr($slug, 0, -strlen('.index.index'));
                if ($this->hasCrudChildren($resourceSlug . '.index', $allPermissionSlugs)) {
                    $visibleMenuUris[] = substr($slug, 0, -strlen('.index.index'));
                    continue;
                }
            }

            if (str_ends_with($slug, '.index')) {
                $visibleMenuUris[] = substr($slug, 0, -strlen('.index'));
                continue;
            }

            // Some workflow pages are keyed to a store route instead of a
            // resource URI; they use that resource's create/update grant.
            if (str_ends_with($slug, '.store')) {
                $resourceSlug = 'admin_api.' . substr($slug, 0, -strlen('.store'));
                if ($this->hasCrudChildren($resourceSlug, $allPermissionSlugs)
                    && isset($allPermissionSlugs[$resourceSlug . '.create'])) {
                    $visibleMenuUris[] = $slug;
                    continue;
                }
            }

            if (str_ends_with($slug, '.create')) {
                $resourceSlug = 'admin_api.' . substr($slug, 0, -strlen('.create'));
                if ($this->hasCrudChildren($resourceSlug, $allPermissionSlugs)) {
                    $visibleMenuUris[] = substr($slug, 0, -strlen('.create')) . '.store';
                }
            }

            if (preg_match('/\\.(create|store|update|show|edit|destroy|export|import)$/', $slug)) {
                continue;
            }

            // A non-resource parent organizes the permission tree and does not grant read access.
            if (isset($allPermissionSlugs['admin_api.' . $slug . '.index'])) {
                continue;
            }

            // Standalone pages use their own permission as the read permission.
            $visibleMenuUris[] = $slug;
        }

        return array_values(array_unique($visibleMenuUris));
    }

    /**
     * Return the operation permissions used by a menu item.
     *
     * @param string $menuUri
     * @param array<string, bool> $assignedPermissionSlugs
     * @param array<string, bool> $allPermissionSlugs
     * @param bool $isOwner
     * @return array<string, bool>
     */
    public function actions(
        string $menuUri,
        array $assignedPermissionSlugs,
        array $allPermissionSlugs,
        bool $isOwner
    ): array {
        if ($isOwner) {
            return [
                'view' => true,
                'show' => true,
                'create' => true,
                'update' => true,
                'delete' => true,
                'export' => true,
            ];
        }

        $resourceSlug = 'admin_api.' . $menuUri;

        if ($this->hasCrudChildren($resourceSlug . '.index', $allPermissionSlugs)) {
            $resourceSlug .= '.index';
        }

        if (str_ends_with($menuUri, '.store')) {
            $resourceSlug = 'admin_api.' . substr($menuUri, 0, -strlen('.store'));
            $hasCreatePermission = isset($assignedPermissionSlugs[$resourceSlug . '.create']);

            return [
                'view' => $hasCreatePermission,
                'show' => $hasCreatePermission,
                'create' => $hasCreatePermission,
                'update' => $hasCreatePermission,
                'delete' => false,
                'export' => false,
            ];
        }

        if (isset($assignedPermissionSlugs[$resourceSlug])
            && $this->hasCrudChildren($resourceSlug, $allPermissionSlugs)) {
            return [
                'view' => true,
                'show' => true,
                'create' => true,
                'update' => true,
                'delete' => true,
                'export' => true,
            ];
        }

        $hasListPermission = isset($allPermissionSlugs[$resourceSlug . '.index']);
        $viewSlug = $hasListPermission ? $resourceSlug . '.index' : $resourceSlug;

        $showSlug = isset($allPermissionSlugs[$resourceSlug . '.show'])
            ? $resourceSlug . '.show'
            : ($hasListPermission ? $resourceSlug . '.index' : null);

        return [
            'view' => isset($assignedPermissionSlugs[$viewSlug]),
            'show' => $showSlug !== null && isset($assignedPermissionSlugs[$showSlug]),
            'create' => isset($assignedPermissionSlugs[$resourceSlug . '.create']),
            'update' => isset($assignedPermissionSlugs[$resourceSlug . '.create']),
            'delete' => isset($assignedPermissionSlugs[$resourceSlug . '.destroy']),
            'export' => isset($assignedPermissionSlugs[$resourceSlug . '.export']),
        ];
    }

    /**
     * Determine whether a permission is a resource parent with operation children.
     *
     * @param array<string, bool> $allPermissionSlugs
     */
    private function hasCrudChildren(string $resourceSlug, array $allPermissionSlugs): bool
    {
        $count = 0;
        foreach (['index', 'create', 'destroy', 'export'] as $operation) {
            if (isset($allPermissionSlugs[$resourceSlug . '.' . $operation])) {
                $count++;
            }
        }

        return $count >= 2;
    }
}
