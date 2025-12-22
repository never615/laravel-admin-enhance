<?php
/**
 * Copyright (c) 2018. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
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
use Mallto\Admin\Data\Administrator;
use Mallto\Admin\Data\Role;
use Mallto\Tool\Exception\PermissionDeniedException;
use Mallto\Tool\Exception\ResourceException;

class AdminUserController extends Controller
{
    public function index(Request $request): \Illuminate\Pagination\LengthAwarePaginator
    {
        $adminUser = $this->authUser();
        $subjectId = $adminUser->subject_id;

        $query = Administrator::query()
            ->with('roles:id,name')
            ->where('subject_id', $subjectId);

        if ($request->filled('username')) {
            $query->where('username', 'ilike', "%{$request->username}%");
        }
        if ($request->filled('name')) {
            $query->where('name', 'ilike', "%{$request->name}%");
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = (int)($request->get('per_page', 20));
        return $query->orderByDesc('id')->paginate($perPage);

    }

    public function store(Request $request): JsonResponse
    {
        $adminUser = $this->authUser();
        $payload = $this->validateUser($request);
        $subjectId = $adminUser->subject_id;
        $payload['subject_id'] = $subjectId;
        $payload['password'] = bcrypt($payload['password']);

        $this->assertUsernameUnique($payload['username'], $subjectId);

        DB::transaction(function () use (&$model, $payload, $request) {
            $model = Administrator::query()->create(Arr::except($payload, ['roles']));
            if (!empty($request->roles)) {
                $model->roles()->sync($request->roles);
            }
        });

        return response()->json($model->load('roles:id,name'), 201);
    }

    public function show($id): JsonResponse
    {
        $adminUser = $this->authUser();
        $model = $this->findUserBySubject($id, $adminUser->subject_id);

        return response()->json($model->load('roles:id,name'));
    }

    public function update(Request $request, $id): JsonResponse
    {
        $adminUser = $this->authUser();
        $model = $this->findUserBySubject($id, $adminUser->subject_id);

        $payload = $this->validateUser($request, $model->id, false);

        if (!empty($payload['password'])) {
            $payload['password'] = bcrypt($payload['password']);
        } else {
            unset($payload['password']);
        }

        $this->assertUsernameUnique($payload['username'] ?? $model->username, $model->subject_id, $model->id);

        DB::transaction(function () use ($model, $payload, $request, $adminUser) {
            if ($adminUser->id === $model->id && $request->filled('roles')) {
                throw new PermissionDeniedException('Self roles update is not allowed');
            }

            $model->update(Arr::except($payload, ['roles']));
            if ($request->has('roles')) {
                $model->roles()->sync(array_filter((array)$request->roles));
            }
        });

        return response()->json($model->fresh()->load('roles:id,name'));
    }

    public function destroy($id): JsonResponse
    {
        $adminUser = $this->authUser();
        $model = $this->findUserBySubject($id, $adminUser->subject_id);

        if ($adminUser->id === $model->id) {
            throw new PermissionDeniedException('Self delete is not allowed');
        }

        $model->delete();

        return response()->json(['msg' => 'Deleted']);
    }


    protected function authUser(): Administrator
    {
        $adminUser = Auth::guard('admin_api')->user();
        if (!$adminUser) {
            throw new PermissionDeniedException('Not authenticated');
        }

        return $adminUser;
    }

    protected function validateUser(Request $request, $id = null, $isCreate = true): array
    {
        $rules = [
            'username' => ['sometimes', 'required', 'string'],
            'name' => ['sometimes', 'required', 'string'],
            'mobile' => ['nullable', 'string'],
            'status' => ['nullable', 'in:normal,forbidden'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['integer', 'exists:' . (new Role)->getTable() . ',id'],
        ];

        if ($isCreate) {
            $rules['username'][0] = 'required';
            $rules['name'][0] = 'required';
            $rules['password'] = ['required', 'string', 'min:6'];
        } else {
            $rules['password'] = ['nullable', 'string', 'min:6'];
        }

        $data = $request->all();
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            throw new ResourceException($validator->errors()->first());
        }

        $validated = $validator->validated();

        if (!array_key_exists('roles', $validated)) {
            $validated['roles'] = $request->has('roles') ? (array)$request->roles : null;
        }

        return $validated;
    }

    protected function findUserBySubject($id, $subjectId): Administrator
    {
        $model = Administrator::query()
            ->where('subject_id', $subjectId)
            ->find($id);

        if (!$model) {
            throw new ModelNotFoundException();
        }

        return $model;
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
