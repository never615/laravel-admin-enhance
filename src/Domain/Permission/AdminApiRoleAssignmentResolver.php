<?php

namespace Mallto\Admin\Domain\Permission;

/**
 * Checks whether an account may assign roles with the permissions they grant.
 */
class AdminApiRoleAssignmentResolver
{
    private $delegationResolver;
    private $systemRoleSlugs;

    public function __construct(
        ?AdminApiPermissionDelegationResolver $delegationResolver = null,
        array $systemRoleSlugs = []
    )
    {
        $this->delegationResolver = $delegationResolver ?: new AdminApiPermissionDelegationResolver();
        $this->systemRoleSlugs = $systemRoleSlugs;
    }

    /**
     * @param array<int, array{id: int, slug: string, parent_id: int|null}> $permissions
     * @param string[] $operatorPermissionSlugs
     * @param array<int, array{slug: string, permission_slugs: string[]}> $targetRoles
     */
    public function canAssign(
        array $permissions,
        array $operatorPermissionSlugs,
        array $targetRoles,
        bool $isOwner = false
    ): bool {
        foreach ($targetRoles as $targetRole) {
            if (in_array($targetRole['slug'], $this->systemRoleSlugs, true)) {
                return false;
            }
        }

        if ($isOwner) {
            return true;
        }

        $operatorGrantableSlugs = array_fill_keys(
            $this->delegationResolver->allowedSlugs($permissions, $operatorPermissionSlugs),
            true
        );

        foreach ($targetRoles as $targetRole) {
            $targetEffectiveSlugs = $this->delegationResolver->allowedSlugs(
                $permissions,
                $targetRole['permission_slugs']
            );

            foreach ($targetEffectiveSlugs as $permissionSlug) {
                if (!isset($operatorGrantableSlugs[$permissionSlug])) {
                    return false;
                }
            }
        }

        return true;
    }
}
