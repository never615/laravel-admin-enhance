<?php

namespace Mallto\Admin\Data\Traits;

use Illuminate\Support\Collection;
use Mallto\Admin\Data\AdminApiPermission;
use Mallto\Admin\Data\Permission;
use Mallto\Admin\Domain\Permission\AdminApiPermissionGrantResolver;

trait HasPermissions2
{

    /**
     * 查询用户的前端接口权限,通过用户的角色关联查询
     *
     * @return mixed
     */
    public function allFrontPermissions(): Collection
    {
        return $this->roles()
            ->where('subject_id', $this->subject_id)
            ->where('pure_front', true)
            ->with('frontApiPermissions')
            ->get()
            ->pluck('frontApiPermissions')
            ->flatten();
    }


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
        //用户直接关联权限没有时候,只能通过角色关联.  admin_user_permissions表未使用
//        if (method_exists($this, 'permissions')) {
//            if ($this->permissions()->where('slug', $permissionSlug)->exists()) {
//                return true;
//            }
//        }

        //3.用户拥有该权限的父权限,通过
        //先查询该权限的父权限,因为权限支持多级,所以要查询出该权限的所有长辈权限
        $permission = Permission::where('slug', $permissionSlug)->first();
        if (!$permission) {
            return false;
        }
        //查询该权限的父权限
        $elderPermissions = $permission->elderPermissions();
        //检查用户的权限中有没有父权限
//        if ($elderPermissions && $this->permissions()->whereIn("id",
//                $elderPermissions->pluck("id"))->exists()) {
//            return true;
//        }

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
            (array)$permission->slug);

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


    /**
     * Check if user has permission.
     *
     * @param       $permissionSlug
     *
     * @param array $arguments
     *
     * @return bool
     */
    public function canApi($permissionSlug, $arguments = []): bool
    {
        //1.项目拥有者拥有全部权限
        if ($this->isOwner()) {
            return true;
        }


        //3.前端 API 权限优先按具体操作检查；资源父权限保留旧配置的全操作授权。
        $permission = AdminApiPermission::query()->where('slug', $permissionSlug)->first();
        if (!$permission) {
            return false;
        }
        if ($permission->common) {
            return true;
        }

        $assignedPermissionSlugs = $this->roles()
            ->where('subject_id', $this->subject_id)
            ->where('pure_front', true)
            ->with('frontApiPermissions')
            ->get()
            ->pluck('frontApiPermissions')
            ->flatten()
            ->pluck('slug')
            ->all();
        $elderPermissions = $permission->elderPermissions();
        if ($permission->parent_id && !$elderPermissions->contains('id', $permission->parent_id)) {
            $parentPermission = AdminApiPermission::query()->find($permission->parent_id);
            if ($parentPermission) {
                $elderPermissions->push($parentPermission);
            }
        }

        $resourceParentSlugs = [];
        foreach ($elderPermissions as $elderPermission) {
            $operationSlugs = array_map(function ($operation) use ($elderPermission) {
                return $elderPermission->slug . '.' . $operation;
            }, ['index', 'create', 'destroy', 'export']);

            if (AdminApiPermission::query()
                ->where('parent_id', $elderPermission->id)
                ->whereIn('slug', $operationSlugs)
                ->count() >= 2) {
                $resourceParentSlugs[] = $elderPermission->slug;
            }
        }

        return app(AdminApiPermissionGrantResolver::class)->isGranted(
            $permission->slug,
            $assignedPermissionSlugs,
            $resourceParentSlugs
        );
    }


    public function isOwner()
    {
        return $this->roles()
            ->where('subject_id', $this->subject_id)
            ->where('slug', config("admin.roles.owner"))
            ->exists();
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
