<?php

namespace Mallto\Admin\Domain\Permission;

/**
 * Resolves explicit operation grants and legacy resource-parent grants.
 */
class AdminApiPermissionGrantResolver
{
    /**
     * @param string[] $assignedPermissionSlugs
     * @param string[] $grantingParentSlugs Resource parents with CRUD child permissions.
     */
    public function isGranted(
        string $permissionSlug,
        array $assignedPermissionSlugs,
        array $grantingParentSlugs
    ): bool {
        if (in_array($permissionSlug, $assignedPermissionSlugs, true)) {
            return true;
        }

        return (bool)array_intersect($grantingParentSlugs, $assignedPermissionSlugs);
    }
}
