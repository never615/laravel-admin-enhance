<?php

namespace Mallto\Admin\Data\Traits;

use Illuminate\Support\Collection;
use Mallto\Admin\Data\AdminApiPermission;

trait HasFrontPermissions
{
    /**
     * Get all permissions of user.
     *
     * @return mixed
     */
    public function allPermissions(): Collection
    {
        return $this->roles()->with('permissions')->get()->pluck('permissions')->flatten()->merge($this->permissions);
    }

    /**
     * Check if user has permission.
     *
     * @param $permissionSlug
     * @param array $arguments
     *
     * @return bool
     */
    public function can($permissionSlug, $arguments = []): bool
    {
        //管理员角色拥有全部权限
        if ($this->isAdministrator()) {
            return true;
        }


        //查询当前权限
        $permission = AdminApiPermission::query()->where('slug', $permissionSlug)->first();
        if (!$permission) {
            return false;
        }
        //查询该权限的父权限,因为权限支持多级,所以要查询出该权限的所有长辈权限
        $elderPermissions = $permission->elderPermissions();

        //用户的角色拥有该权限通过,用户的角色拥有该权限的父权限,通过
        $waiteVerifyPermissionSlugs = array_merge($elderPermissions->pluck("slug")->toArray(),
            (array)$permission->slug);

        $roleApiPermissionSlugs = $this->roles->pluck('permissions')->flatten()->pluck('slug');

        foreach ($waiteVerifyPermissionSlugs as $waiteVerifyPermissionSlug) {
            if ($roleApiPermissionSlugs->contains($waiteVerifyPermissionSlug)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has no permission.
     *
     * @param $permission
     *
     * @return bool
     */
    public function cannot(string $permission): bool
    {
        return !$this->can($permission);
    }

    /**
     * Check if user is administrator.
     *
     * @return mixed
     */
    public function isAdministrator(): bool
    {
        return $this->isRole('administrator');
    }

    /**
     * Check if user is $role.
     *
     * @param string $role
     *
     * @return mixed
     */
    public function isRole(string $role): bool
    {
        return $this->roles->pluck('slug')->contains($role);
    }

    /**
     * Check if user in $roles.
     *
     * @param array $roles
     *
     * @return mixed
     */
    public function inRoles(array $roles = []): bool
    {
        return $this->roles->pluck('slug')->intersect($roles)->isNotEmpty();
    }

    /**
     * If visible for roles.
     *
     * @param $roles
     *
     * @return bool
     */
    public function visible(array $roles = []): bool
    {
        if (empty($roles)) {
            return true;
        }

        $roles = array_column($roles, 'slug');

        return $this->inRoles($roles) || $this->isAdministrator();
    }

    /**
     * Detach models from the relationship.
     *
     * @return void
     */
    protected static function bootHasPermissions()
    {
        static::deleting(function ($model) {
            $model->roles()->detach();

            $model->permissions()->detach();
        });
    }
}
