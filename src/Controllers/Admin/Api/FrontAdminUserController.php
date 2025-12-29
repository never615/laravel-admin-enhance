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
use Mallto\Admin\Data\Administrator;
use Mallto\Admin\Data\Role;
use Mallto\Tool\Exception\PermissionDeniedException;
use Mallto\Tool\Exception\ResourceException;

class FrontAdminUserController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::guard('admin_api')->user();

        $query = $this->queryBuilder()
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

        DB::transaction(function () use (&$model, $payload, $request) {
            $model = Administrator::query()->create(Arr::except($payload, ['roles']));
            if (!empty($request->roles)) {
                $model->roles()->sync($request->roles);
            }
        });

        $model = $this->findUserBySubject($model->id, $user->subject_id);

        return response()->json($model->load('roles:id,name'), 201);
    }

    public function show($id): JsonResponse
    {
        $user = Auth::guard('admin_api')->user();
        $model = $this->findUserBySubject($id, $user->subject_id);

        return response()->json($model->load('roles:id,name'));
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

        DB::transaction(function () use ($model, $payload, $request, $user) {
            if ($user->id === $model->id && $request->filled('roles')) {
                throw new PermissionDeniedException('Self roles update is not allowed');
            }

            $model->update(Arr::except($payload, ['roles']));
            if ($request->has('roles')) {
                $model->roles()->sync(array_filter((array)$request->roles));
            }
        });

        $model = $this->findUserBySubject($model->id, $user->subject_id);

        return response()->json($model->load('roles:id,name'));
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
        $rules = [
            'username' => ['sometimes', 'required', 'string'],
            'name' => ['sometimes', 'required', 'string'],
//            'mobile' => ['nullable', 'string'],
//            'status' => ['nullable', 'in:normal,forbidden'],
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

        $validator = Validator::make($request->all(), $rules);
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
        $model = $this->queryBuilder()
            ->where('subject_id', $subjectId)
            ->find($id);

        if (!$model) {
            throw new ModelNotFoundException();
        }

        return $model;
    }

    protected function queryBuilder()
    {
        return Administrator::query()
            ->select(['id', 'username', 'name', 'created_at', 'updated_at', 'subject_id'])
            ->with('roles:id,name')
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

