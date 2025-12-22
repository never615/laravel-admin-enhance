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
use Mallto\Admin\AdminUtils;
use Mallto\Admin\Data\AdminApiPermission;
use Mallto\Admin\Data\Role;
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
class RoleController extends Controller
{
    public function index(Request $request): \Illuminate\Pagination\LengthAwarePaginator
    {
        $adminUser = $this->authUser();
        $subjectId = $adminUser->subject_id;

        $query = Role::query()
            ->with([
//                'permissions:id,name,slug',
//                'apiPermissions:id,name,slug',
//                'frontMenus:id,title,uri'
            ])
            ->where('subject_id', $subjectId);

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

        return $this->respondWithRole($role);
    }

    public function store(Request $request): JsonResponse
    {
        $adminUser = $this->authUser();
        $payload = $this->validateRole($request);
        $payload['subject_id'] = $adminUser->subject_id;

        $model = null;
        DB::transaction(function () use (&$model, $payload, $request) {
            $model = Role::query()->create(Arr::except($payload, ['permissions', 'api_permissions', 'front_menus']));
            $this->syncRelations($model, $request);
        });

        AdminUtils::clearMenuCache();

        return $this->respondWithRole($model->fresh(), 201);
    }


    public function update(Request $request, $id): JsonResponse
    {
        $adminUser = $this->authUser();
        $model = $this->findRoleBySubject($id, $adminUser->subject_id);

        $this->guardSystemRoles($model);

        $payload = $this->validateRole($request, false, $model->id);

        DB::transaction(function () use ($model, $payload, $request) {
            $model->update(Arr::except($payload, ['api_permissions']));
            $this->syncRelations($model, $request);
        });

        AdminUtils::clearMenuCache();

        return $this->respondWithRole($model->fresh());
    }

    public function destroy($id): \Illuminate\Http\Response
    {
        $adminUser = $this->authUser();
        $model = $this->findRoleBySubject($id, $adminUser->subject_id);

        $this->guardSystemRoles($model);

        if ($adminUser->isRole($model->slug)) {
            throw new PermissionDeniedException('Cannot delete current user role');
        }

        $model->delete();
        AdminUtils::clearMenuCache();

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

    protected function validateRole(Request $request, bool $isCreate = true, $roleId = null): array
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

        if (!isset($validated['slug']) || empty($validated['slug'])) {
            $validated['slug'] = $this->generateSlug($validated['name'], $roleId);
        }

        return $validated;
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

    protected function syncRelations(Role $role, Request $request): void
    {
        if ($request->has('api_permissions')) {
            $role->apiPermissions()->sync(array_filter((array)$request->api_permissions));
        }
    }

    protected function findRoleBySubject($id, $subjectId, $withPermission = false): Role
    {
        $query = Role::query()
            ->where('subject_id', $subjectId);

        if ($withPermission) {
            $query->with(['apiPermissions:id,name,slug']);
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

    protected function respondWithRole(Role $role, $status = 200): JsonResponse
    {
//        $role->load(['apiPermissions:id,name,slug']);

        $allPermissions = AdminApiPermission::query()
            ->select('admin_api_permissions.id', 'admin_api_permissions.name', 'admin_api_permissions.slug')
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
}
