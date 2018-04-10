<?php

namespace Mallto\Admin\Data\Traits;


use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Mallto\Admin\Data\Permission;

trait HasPermissions
{


    protected $tempPermissions;

    /**
     * Get avatar attribute.
     *
     * @param string $avatar
     *
     * @return string
     */
    public function getAvatarAttribute($avatar)
    {
        if ($avatar) {
            return Storage::disk(config('admin.upload.disk'))->url($avatar);
        }

        return admin_asset('/vendor/laravel-admin/AdminLTE/dist/img/user2-160x160.jpg');
    }

    /**
     * A user has and belongs to many roles.
     *
     * @return BelongsToMany
     */
    public function roles() : BelongsToMany
    {
        $pivotTable = config('admin.database.role_users_table');

        $relatedModel = config('admin.database.roles_model');

        return $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'role_id');
    }

    /**
     * A User has and belongs to many permissions.
     *
     * @return BelongsToMany
     */
    public function permissions() : BelongsToMany
    {
        $currentId = $this->id;
        if ($this->tempPermissions && isset($this->tempPermissions[$currentId])) {
            return $this->tempPermissions[$currentId];
        }

        $pivotTable = config('admin.database.user_permissions_table');

        $relatedModel = config('admin.database.permissions_model');

        $permissions = $this->belongsToMany($relatedModel, $pivotTable, 'user_id', 'permission_id');
        $this->tempPermissions[$currentId] = $permissions;

        return $permissions;
    }

    /**
     * Get all permissions of user.
     *
     * @return mixed
     */
    public function allPermissions() : Collection
    {
        return $this->roles()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->merge($this->permissions);
    }

    /**
     * Check if user has permission.
     *
     * @param $permissionSlug
     *
     * @return bool
     */
    public function can(string $permissionSlug) : bool
    {
        //1.项目拥有者拥有全部权限
        if ($this->isOwner()) {
            return true;
        }


        //2.用户拥有该权限通过
        if (method_exists($this, 'permissions')) {
            if ($this->permissions()->where('slug', $permissionSlug)->exists()) {
//            if ($this->permissions->keyBy('slug')->has($permissionSlug)) {
                return true;
            }
        }
        //3.用户拥有该权限的父权限,通过
        //先查询该权限的父权限,因为权限支持多级,所以要查询出该权限的所有长辈权限
        $permission = Permission::where('slug', $permissionSlug)->first();
        if (!$permission) {
            return false;
        }
        $elderPermissions = $permission->elderPermissions();
        //检查用户的权限中有没有$elderPermissions中的权限
        if ($elderPermissions && $this->permissions()->whereIn("id", $elderPermissions->pluck("id"))->exists()) {
            return true;
        }

        //4.用户的角色拥有该权限通过
        //5.用户的角色拥有该权限的父权限,通过
        foreach ($this->roles as $role) {
            if ($role->can($permissionSlug)) {
                return true;
            }
            if ($elderPermissions && $role->permissions()->whereIn("id", $elderPermissions->pluck("id"))->exists()) {
                return true;
            }
        }

        return $this->roles->pluck('permissions')->flatten()->pluck('slug')->contains($permission);
    }

    /**
     * Check if user has no permission.
     *
     * @param $permission
     *
     * @return bool
     */
    public function cannot(string $permission) : bool
    {
        return !$this->can($permission);
    }

    /**
     * Check if user is administrator.
     *
     * @return mixed
     */
    public function isAdministrator() : bool
    {
        return $this->isRole(config("admin.roles.admin"));
    }

    public function isOwner()
    {
        return $this->isRole(config("admin.roles.owner"));
    }


    /**
     * Check if user is $role.
     *
     * @param string $role
     *
     * @return mixed
     */
    public function isRole(string $role) : bool
    {
        return $this->roles()
            ->where('slug', $role)
            ->exists();
//        return $this->roles->keyBy('slug')->has($role);
    }

    /**
     * Check if user in $roles.
     *
     * @param array $roles
     *
     * @return mixed
     */
    public function inRoles(array $roles = []) : bool
    {
        return $this->roles()
            ->whereIn('slug', (array) $roles)->exists();
    }

    /**
     * If visible for roles.
     *
     * @param $roles
     *
     * @return bool
     */
    public function visible(array $roles = []) : bool
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
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->roles()->detach();

            $model->permissions()->detach();
        });
    }

}
