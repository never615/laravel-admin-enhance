<?php
/*
 * Copyright (c) 2025. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Mallto\Admin\Data\AdminApiPermission;
use Mallto\Tool\Exception\PermissionDeniedException;

class FrontPermissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authUser();

        $query = AdminApiPermission::query()
            ->select(['id', 'name', 'slug', 'parent_id', 'order', 'path', 'created_at', 'updated_at']);

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($subQuery) use ($keyword) {
                $subQuery->where('name', 'ilike', "%$keyword%")
                    ->orWhere('slug', 'ilike', "%$keyword%");
            });
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', (int)$request->parent_id);
        }

        $permissions = $query
            ->orderBy('order')
//            ->orderBy('parent_id')
//            ->orderBy('id')
            ->get();

        if ($request->boolean('as_tree')) {
            $permissions = $this->buildTree($permissions);
        }

        return response()->json($permissions);
    }

    protected function buildTree(Collection $permissions): Collection
    {
        $grouped = $permissions->groupBy(function ($item) {
            return $item->parent_id ?? 0;
        });

        $build = function ($parentId) use (&$build, $grouped) {
            return $grouped->get($parentId, collect())->map(function ($permission) use (&$build) {
                $permission->children = $build($permission->id);
                return $permission;
            })->values();
        };

        return $build(0);
    }

    protected function authUser()
    {
        $adminUser = Auth::guard('admin_api')->user();
        if (!$adminUser) {
            throw new PermissionDeniedException('Not authenticated');
        }

        return $adminUser;
    }
}
