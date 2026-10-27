<?php

namespace Mallto\Admin\Domain\Permission;

/**
 * Resolves which API permissions an account may delegate to another role.
 */
class AdminApiPermissionDelegationResolver
{
    /**
     * @param array<int, array{id: int, slug: string, parent_id: int|null, common?: bool}> $permissions
     * @param string[] $assignedSlugs
     * @return string[]
     */
    public function allowedSlugs(array $permissions, array $assignedSlugs, bool $isOwner = false): array
    {
        if ($isOwner) {
            return array_values(array_unique(array_column($permissions, 'slug')));
        }

        $assigned = array_fill_keys($assignedSlugs, true);
        $allowed = $assigned;
        $permissionsBySlug = [];
        $childrenByParent = [];
        foreach ($permissions as $permission) {
            $permissionsBySlug[$permission['slug']] = $permission;
            $childrenByParent[(int)($permission['parent_id'] ?? 0)][] = $permission;
            // Common permissions are effective for every API account, even when
            // they are not present in the role pivot.
            if (!empty($permission['common'])) {
                $allowed[$permission['slug']] = true;
            }
        }

        $pending = $assignedSlugs;
        $visited = [];
        while ($pending) {
            $slug = array_pop($pending);
            if (isset($visited[$slug]) || !isset($permissionsBySlug[$slug])) {
                continue;
            }
            $visited[$slug] = true;
            $parentPermission = $permissionsBySlug[$slug];
            $children = $childrenByParent[(int)$parentPermission['id']] ?? [];

            $crudChildren = array_intersect(
                array_column($children, 'slug'),
                array_map(static function ($operation) use ($parentPermission) {
                    return $parentPermission['slug'] . '.' . $operation;
                }, ['index', 'create', 'destroy', 'export'])
            );
            // Explicit group/resource grants include their descendants. A single
            // operation such as `.index` never grants sibling write operations.
            // Resource parents whose name ends in `.index` are recognized by
            // their CRUD children, rather than treated as a read operation.
            $hasCrudChild = count($crudChildren) >= 2;

            if (!$hasCrudChild
                && preg_match('/\.(index|show|edit|create|store|update|destroy|export|import|sync)$/', $slug)) {
                continue;
            }

            foreach ($children as $child) {
                $allowed[$child['slug']] = true;
                $pending[] = $child['slug'];
            }
        }

        return array_keys($allowed);
    }
}
