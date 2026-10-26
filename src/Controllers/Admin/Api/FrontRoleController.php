<?php
/*
 * Copyright (c) 2025. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Mallto\Admin\CacheUtils;
use Mallto\Admin\Data\AdminApiPermission;
use Mallto\Admin\Data\Role;
use Mallto\Admin\Domain\Permission\AdminApiPermissionDelegationResolver;
use Mallto\Tool\Exception\PermissionDeniedException;
use Mallto\Tool\Exception\ResourceException;

/**
 * 纯前端管理页面角色管理使用的接口
 * 纯前端管理页面的角色关联的权限是api_permissions表.
 * 后端会根据角色关联的api_permissions来控制接口访问权限.
 * 并且前端页面的菜单显示权限也是根据api_permissions来控制.(如控制一些按钮的显示隐藏)
 *
 * 此外: 后端会根据角色关联的 api_permissions 来生成菜单缓存,从而控制菜单的显示隐藏.
 * (目前是直接配置角色对应的 front_menus表,后续角色只关联 api_permissions,通过权限来动态匹配 front_menus,来生成菜单返回)
 * Class RoleController
 */
class FrontRoleController extends Controller
{
    public function index(Request $request): \Illuminate\Pagination\LengthAwarePaginator
    {
        $adminUser = $this->authUser();
        $subjectId = $adminUser->subject_id;

        $query = Role::query()
            ->where('subject_id', $subjectId)
            ->where('pure_front', true);

        if ($request->filled('name')) {
            $query->where('name', 'ilike', "%{$request->name}%");
        }

        $perPage = (int)$request->get('per_page', 20);
        return $query->orderByDesc('id')->paginate($perPage);
    }

    public function show($id): JsonResponse
    {
        $adminUser = $this->authUser();
        $role = $this->findRoleBySubject($id, $adminUser->subject_id, true);

        return $this->respondWithRole($role, 200, $adminUser);
    }

    public function store(Request $request): JsonResponse
    {
        $adminUser = $this->authUser();
        $payload = $this->validateRole($request, true, null, $adminUser);
        $payload['subject_id'] = $adminUser->subject_id;
        $payload['pure_front'] = true;

        $model = null;
        DB::transaction(function () use (&$model, $payload) {
            $model = Role::query()->create(Arr::except($payload, ['api_permissions']));
            $this->syncRelations($model, $payload);
        });

        CacheUtils::clearMenuCache();

        return $this->respondWithRole($model->fresh(), 201, $adminUser);
    }


    public function update(Request $request, $id): JsonResponse
    {
        $adminUser = $this->authUser();
        $model = $this->findRoleBySubject($id, $adminUser->subject_id);

        $this->guardSystemRoles($model);

        $payload = $this->validateRole($request, false, $model->id, $adminUser);

        DB::transaction(function () use ($model, $payload) {
            $model->update(Arr::except($payload, ['api_permissions']));
            $this->syncRelations($model, $payload);
        });

        CacheUtils::clearMenuCache();

        return $this->respondWithRole($model->fresh(), 200, $adminUser);
    }

    public function destroy($id): \Illuminate\Http\Response
    {
        $adminUser = $this->authUser();
        $model = $this->findRoleBySubject($id, $adminUser->subject_id);

        $this->guardSystemRoles($model);

        if ($adminUser->isRole($model->slug)) {
            throw new PermissionDeniedException('Cannot delete current user role');
        }

        DB::transaction(function () use ($model) {
            $model->administrators()->detach();
            $model->frontApiPermissions()->detach();
            $model->frontMenus()->detach();
            $model->delete();
        });
        CacheUtils::clearMenuCache();

        return response()->noContent();
    }

    protected function authUser()
    {
        $adminUser = Auth::guard('admin_api')->user();
        if (!$adminUser) {
            throw new PermissionDeniedException('Not authenticated');
        }

        return $adminUser;
    }

    protected function validateRole(
        Request $request,
        bool $isCreate = true,
        $roleId = null,
        $adminUser = null
    ): array
    {
        $rules = [
            'name' => [$isCreate ? 'required' : 'sometimes', 'string'],
            'slug' => ['nullable', 'string'],
            'describe' => ['nullable', 'string'],
            'api_permissions' => ['nullable', 'array'],
            'api_permissions.*' => ['integer', 'exists:' . (new AdminApiPermission)->getTable() . ',id'],
        ];

        $data = $request->all();
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ResourceException($validator->errors()->first());
        }

        $validated = $validator->validated();

        if (array_key_exists('slug', $validated)) {
            $validated['slug'] = trim((string)$validated['slug']);
            if ($validated['slug'] === '') {
                unset($validated['slug']);
            }
        }

        if (array_key_exists('api_permissions', $validated)) {
            $permissionIds = array_values(array_unique(array_map(
                'intval',
                array_filter((array)$validated['api_permissions'], static function ($id) {
                    return $id !== null && $id !== '';
                })
            )));
            $allowedPermissionIds = $this->allowedPermissionIds($adminUser ?: $this->authUser());
            if (array_diff($permissionIds, $allowedPermissionIds)) {
                throw new PermissionDeniedException('权限不足');
            }
            $validated['api_permissions'] = $permissionIds;
        }

        if (!isset($validated['slug']) || empty($validated['slug'])) {
            if (!$isCreate && $roleId) {
                $existingRole = $this->findRoleBySubject(
                    $roleId,
                    ($adminUser ?: $this->authUser())->subject_id
                );
                $validated['slug'] = $existingRole->slug;
            } else {
                $validated['slug'] = $this->generateSlug($validated['name'], $roleId);
            }
        }

        if (in_array($validated['slug'], $this->systemRoleSlugs(), true)) {
            throw new PermissionDeniedException('System role slug is reserved');
        }

        $slugQuery = Role::query()
            ->where('subject_id', $adminUser ? $adminUser->subject_id : $this->authUser()->subject_id)
            ->where('slug', $validated['slug']);
        if ($roleId) {
            $slugQuery->where('id', '!=', $roleId);
        }
        if ($slugQuery->exists()) {
            throw new ResourceException('Role slug already exists');
        }

        if (!$isCreate && !array_key_exists('api_permissions', $validated)) {
            $role = Role::query()
                ->where('subject_id', $adminUser->subject_id)
                ->where('pure_front', true)
                ->with('frontApiPermissions:id,slug')
                ->findOrFail($roleId);
            $allowedPermissionIds = $this->allowedPermissionIds($adminUser);
            $allowedPermissionSlugs = AdminApiPermission::query()
                ->whereIn('id', $allowedPermissionIds)
                ->pluck('slug')
                ->all();
            $hiddenPermissionSlugs = array_diff(
                $role->frontApiPermissions->pluck('slug')->all(),
                $allowedPermissionSlugs
            );
            if ($hiddenPermissionSlugs) {
                throw new PermissionDeniedException('角色包含当前账号无权分配的权限，请明确提交权限列表后再修改');
            }
        }

        return $validated;
    }

    protected function systemRoleSlugs(): array
    {
        return array_values(array_filter([
            config('admin.roles.owner'),
            config('admin.roles.admin'),
        ]));
    }

    protected function generateSlug(string $name, $roleId = null): string
    {
        $adminUser = Auth::guard('admin_api')->user();
        $slug = Str::slug($name);
        $query = Role::query()->where('slug', $slug)->where('subject_id', $adminUser->subject_id);

        if ($roleId) {
            $query->where('id', '!=', $roleId);
        }

        if ($query->exists()) {
            $slug .= '-' . uniqid();
        }

        return $slug;
    }

    protected function syncRelations(Role $role, array $payload): void
    {
        if (array_key_exists('api_permissions', $payload)) {
            $permissionIds = array_values(array_unique(array_map('intval', (array)$payload['api_permissions'])));
            // Persist exactly the IDs submitted by the client. Permission-tree
            // parents are not inferred or inserted during role saving.
            $connection = $role->getConnection();
            $connection->table('admin_role_api_permissions')
                ->where('role_id', $role->getKey())
                ->delete();

            if ($permissionIds) {
                $timestamp = now();
                $connection->table('admin_role_api_permissions')->insert(array_map(
                    static function (int $permissionId) use ($role, $timestamp) {
                        return [
                            'role_id' => $role->getKey(),
                            'permission_id' => $permissionId,
                            'created_at' => $timestamp,
                            'updated_at' => $timestamp,
                        ];
                    },
                    $permissionIds
                ));
            }
        }
    }

    protected function findRoleBySubject($id, $subjectId, $withPermission = false): Role
    {
        $query = Role::query()
            ->where('subject_id', $subjectId)
            ->where('pure_front', true);

        if ($withPermission) {
            $query->with(['frontApiPermissions:id,name,slug']);
        }

        $model = $query->find($id);


        if (!$model) {
            throw new ModelNotFoundException();
        }

        return $model;
    }

    protected function guardSystemRoles(Role $role): void
    {
        if (in_array($role->slug, [config('admin.roles.owner'), config('admin.roles.admin')], true)) {
            throw new PermissionDeniedException('System role is immutable');
        }
    }

    protected function respondWithRole(Role $role, $status = 200, $adminUser = null): JsonResponse
    {
//        $role->load(['apiPermissions:id,name,slug']);

        $adminUser = $adminUser ?: $this->authUser();
        $allowedPermissionIds = $this->allowedPermissionIds($adminUser);
        if ($role->relationLoaded('frontApiPermissions')) {
            $role->setRelation(
                'frontApiPermissions',
                $role->frontApiPermissions->whereIn('id', $allowedPermissionIds)->values()
            );
        }

        $allPermissions = AdminApiPermission::query()
            ->select('admin_api_permissions.id', 'admin_api_permissions.name', 'admin_api_permissions.slug')
            ->whereIn('admin_api_permissions.id', $allowedPermissionIds)
            ->selectRaw('CASE WHEN EXISTS (
                SELECT 1 FROM admin_role_api_permissions 
                WHERE admin_role_api_permissions.permission_id = admin_api_permissions.id 
                AND admin_role_api_permissions.role_id = ?
            ) THEN true ELSE false END as "select"', [$role->id])
            ->orderBy('order')
            ->get();


        $role->all_api_permissions = $allPermissions;
        return response()->json($role, $status);
    }

    /**
     * Return the permissions that the current account may grant to another role.
     * Resource parent permissions keep their legacy meaning: all direct CRUD
     * children are grantable. A child permission alone grants only itself.
     *
     * @param mixed $adminUser
     * @return int[]
     */
    protected function allowedPermissionIds($adminUser): array
    {
        $permissions = AdminApiPermission::query()
            ->select('id', 'slug', 'parent_id', 'common')
            ->orderBy('order')
            ->get()
            ->toArray();

        if ($adminUser->isOwner()) {
            return array_map(static function ($permission) {
                return (int)$permission['id'];
            }, $permissions);
        }

        $assignedSlugs = $adminUser->allFrontPermissions()->pluck('slug')->all();
        $allowedSlugs = app(AdminApiPermissionDelegationResolver::class)->allowedSlugs(
            $permissions,
            $assignedSlugs
        );
        $allowedSlugMap = array_fill_keys($allowedSlugs, true);

        return array_values(array_map(static function ($permission) {
            return (int)$permission['id'];
        }, array_filter($permissions, static function ($permission) use ($allowedSlugMap) {
            return isset($allowedSlugMap[$permission['slug']]);
        })));
    }
}
