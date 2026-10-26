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
use Mallto\Admin\CacheUtils;
use Mallto\Admin\Data\Administrator;
use Mallto\Admin\Data\AdminApiPermission;
use Mallto\Admin\Data\Role;
use Mallto\Admin\Domain\Permission\AdminApiRoleAssignmentResolver;
use Mallto\Tool\Exception\PermissionDeniedException;
use Mallto\Tool\Exception\ResourceException;

class FrontAdminUserController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('admin_api')->user();

        $query = $this->queryBuilder((int)$user->subject_id)
            ->where('subject_id', $user->subject_id);

        if ($request->filled('username')) {
            $query->where('username', 'ilike', "%{$request->username}%");
        }
        if ($request->filled('name')) {
            $query->where('name', 'ilike', "%{$request->name}%");
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query->orderByDesc('id')->paginate((int)$request->get('per_page', 20));
    }

    public function store(Request $request): JsonResponse
    {
        $user = Auth::guard('admin_api')->user();

        $payload = $this->validateUser($request, true);
        $payload['subject_id'] = $user->subject_id;
        $payload['password'] = bcrypt($payload['password']);
        $payload['pure_front'] = true;

        $this->assertUsernameUnique($payload['username'], $payload['subject_id']);

        DB::transaction(function () use (&$model, $payload) {
            $model = Administrator::query()->create(Arr::except($payload, ['roles']));
            if (array_key_exists('roles', $payload)) {
                $model->roles()->sync((array)$payload['roles']);
            }
        });

        if (array_key_exists('roles', $payload)) {
            CacheUtils::clearMenuCache();
        }

        $model = $this->findUserBySubject($model->id, $user->subject_id);

        return response()->json($model, 201);
    }

    public function show($id): JsonResponse
    {
        $user = Auth::guard('admin_api')->user();
        $model = $this->findUserBySubject($id, $user->subject_id);

        return response()->json($model);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $user = Auth::guard('admin_api')->user();
        $model = $this->findUserBySubject($id, $user->subject_id);


        $payload = $this->validateUser($request, false);

        if (!empty($payload['password'])) {
            $payload['password'] = bcrypt($payload['password']);
        } else {
            unset($payload['password']);
        }

        $this->assertUsernameUnique($payload['username'] ?? $model->username, $model->subject_id, $model->id);

        DB::transaction(function () use ($model, $payload, $user) {
            if ($user->id === $model->id && array_key_exists('roles', $payload)) {
                throw new PermissionDeniedException('Self roles update is not allowed');
            }

            $model->update(Arr::except($payload, ['roles']));
            if (array_key_exists('roles', $payload)) {
                $model->roles()->sync((array)$payload['roles']);
            }
        });

        if (array_key_exists('roles', $payload)) {
            CacheUtils::clearMenuCache();
        }

        $model = $this->findUserBySubject($model->id, $user->subject_id);

        return response()->json($model);
    }

    public function destroy($id)
    {
        $user = Auth::guard('admin_api')->user();
        $model = $this->findUserBySubject($id, $user->subject_id);

        if ($user->id === $model->id) {
            throw new PermissionDeniedException('Self delete is not allowed');
        }

        $model->delete();

        return response()->noContent();
    }

    protected function validateUser(Request $request, bool $isCreate)
    {
        $input = $request->all();
        if (array_key_exists('role_ids', $input)) {
            if (array_key_exists('roles', $input)
                && $this->normalizeRoleIds($input['roles']) !== $this->normalizeRoleIds($input['role_ids'])) {
                throw new ResourceException('roles and role_ids must match');
            }

            $input['roles'] = $input['role_ids'];
        }

        $rules = [
            'username' => ['sometimes', 'required', 'string'],
            'name' => ['sometimes', 'required', 'string'],
//            'mobile' => ['nullable', 'string'],
//            'status' => ['nullable', 'in:normal,forbidden'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer'],
        ];

        if ($isCreate) {
            $rules['username'][0] = 'required';
            $rules['name'][0] = 'required';
            $rules['password'] = ['required', 'string', 'min:6'];
        } else {
            $rules['password'] = ['nullable', 'string', 'min:6'];
        }

        $validator = Validator::make($input, $rules);
        if ($validator->fails()) {
            throw new ResourceException($validator->errors()->first());
        }

        $validated = $validator->validated();

        if (array_key_exists('roles', $validated)) {
            $validated['roles'] = $this->normalizeRoleIds($validated['roles']);
            $this->assertAssignableRoles($validated['roles'], Auth::guard('admin_api')->user());
        }

        return $validated;
    }

    /**
     * Prevent assigning cross-subject, non-frontend, or over-privileged roles.
     */
    protected function assertAssignableRoles(array $roleIds, $adminUser): void
    {
        if (!$roleIds) {
            return;
        }

        $roles = Role::query()
            ->where('subject_id', $adminUser->subject_id)
            ->where('pure_front', true)
            ->whereIn('id', $roleIds)
            ->with('frontApiPermissions:id,slug')
            ->get();

        if ($roles->count() !== count($roleIds)) {
            throw new PermissionDeniedException('权限不足');
        }

        $permissions = AdminApiPermission::query()
            ->select('id', 'slug', 'parent_id', 'common')
            ->orderBy('order')
            ->get()
            ->toArray();
        $operatorPermissionSlugs = $adminUser->allFrontPermissions()->pluck('slug')->all();
        $targetRoles = $roles->map(static function (Role $role) {
            return [
                'slug' => $role->slug,
                'permission_slugs' => $role->frontApiPermissions->pluck('slug')->all(),
            ];
        })->all();

        $roleAssignmentResolver = new AdminApiRoleAssignmentResolver(
            null,
            array_filter([
                config('admin.roles.owner'),
                config('admin.roles.admin'),
            ])
        );
        if (!$roleAssignmentResolver->canAssign(
            $permissions,
            $operatorPermissionSlugs,
            $targetRoles,
            $adminUser->isOwner()
        )) {
            throw new PermissionDeniedException('权限不足');
        }
    }

    protected function findUserBySubject($id, $subjectId): Administrator
    {
        $model = $this->queryBuilder($subjectId)
            ->where('subject_id', $subjectId)
            ->find($id);

        if (!$model) {
            throw new ModelNotFoundException();
        }

        $model->setAttribute('role_ids', $model->roles->pluck('id')->map(static function ($id) {
            return (int)$id;
        })->all());

        return $model;
    }

    protected function normalizeRoleIds($roleIds): array
    {
        return array_values(array_unique(array_map('intval', (array)$roleIds)));
    }

    protected function queryBuilder(int $subjectId)
    {
        return Administrator::query()
            ->select(['id', 'username', 'name', 'created_at', 'updated_at', 'subject_id'])
            ->with(['roles' => static function ($query) use ($subjectId) {
                $query->select('id', 'name')
                    ->where('subject_id', $subjectId)
                    ->where('pure_front', true);
            }])
            ->where('pure_front', true);
    }

    protected function assertUsernameUnique(string $username, int $subjectId, $ignoreId = null): void
    {
        $query = Administrator::query()
            ->where('subject_id', $subjectId)
            ->where('username', $username);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        if ($query->exists()) {
            throw new ResourceException('Username already exists');
        }
    }
}

