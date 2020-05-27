<?php

namespace Mallto\Admin\Data\Traits;

use Mallto\Admin\Data\Permission;

trait HasPermissions2
{

    /**
     * Check if user has permission.
     *
     * @param       $permissionSlug
     *
     * @param array $arguments
     *
     * @return bool
     */
    public function can($permissionSlug, $arguments = []): bool
    {
        //1.项目拥有者拥有全部权限
        if ($this->isOwner()) {
            return true;
        }

        //2.用户拥有该权限通过
        if (method_exists($this, 'permissions')) {
            if ($this->permissions()->where('slug', $permissionSlug)->exists()) {
                return true;
            }
        }

        //3.用户拥有该权限的父权限,通过
        //先查询该权限的父权限,因为权限支持多级,所以要查询出该权限的所有长辈权限
        $permission = Permission::where('slug', $permissionSlug)->first();
        if ( ! $permission) {
            return false;
        }
        //查询该权限的父权限
        $elderPermissions = $permission->elderPermissions();
        //检查用户的权限中有没有父权限
        if ($elderPermissions && $this->permissions()->whereIn("id",
                $elderPermissions->pluck("id"))->exists()) {
            return true;
        }

        //4.用户的角色拥有该权限通过
        //5.用户的角色拥有该权限的父权限,通过
//        foreach ($this->roles as $role) {
//            if ($role->can($permissionSlug)) {
//                return true;
//            }
//
//            if ($elderPermissions && $role->permissions()->whereIn("id", $elderPermissions->pluck("id"))->exists()) {
//                return true;
//            }
//        }

//        return false;

        $waiteVerifyPermissionSlugs = array_merge($elderPermissions->pluck("slug")->toArray(),
            (array) $permission->slug);

        $rolePermissionSlugs = $this->roles->pluck('permissions')->flatten()->pluck('slug');

        foreach ($waiteVerifyPermissionSlugs as $waiteVerifyPermissionSlug) {
            if ($rolePermissionSlugs->contains($waiteVerifyPermissionSlug)) {
                return true;
            }
        }

        return false;
//        return $this->roles->pluck('permissions')->flatten()->pluck('slug')
//            ->contains($permission);
    }


    public function isOwner()
    {
        return $this->isRole(config("admin.roles.owner"));
    }


//    /**
//     * Check if user is $role.
//     *
//     * @param string $role
//     *
//     * @return mixed
//     */
//    public function isRole(string $role): bool
//    {
//        return $this->roles()
//            ->where('slug', $role)
//            ->exists();
//    }

//    /**
//     * Check if user in $roles.
//     *
//     * @param array $roles
//     *
//     * @return mixed
//     */
//    public function inRoles(array $roles = []): bool
//    {
//        return $this->roles()
//            ->whereIn('slug', (array) $roles)->exists();
//    }

//    /**
//     * Detach models from the relationship.
//     *
//     * @return void
//     */
//    protected static function boot()
//    {
//        parent::boot();
//
//        static::deleting(function ($model) {
//            $model->roles()->detach();
//
//            $model->permissions()->detach();
//        });
//    }

}
