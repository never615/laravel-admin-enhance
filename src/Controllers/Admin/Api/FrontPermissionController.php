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
use Mallto\Admin\Domain\Permission\AdminApiPermissionDelegationResolver;
use Mallto\Tool\Exception\PermissionDeniedException;

class FrontPermissionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $adminUser = $this->authUser();

        $query = AdminApiPermission::query()
            ->select(['id', 'name', 'slug', 'parent_id', 'order', 'path', 'created_at', 'updated_at']);

        $allPermissions = AdminApiPermission::query()
            ->select('id', 'name', 'slug', 'parent_id', 'common')
            ->orderBy('order')
            ->get()
            ->toArray();
        $assignedSlugs = $adminUser->allFrontPermissions()->pluck('slug')->all();
        $allowedSlugs = app(AdminApiPermissionDelegationResolver::class)->allowedSlugs(
            $allPermissions,
            $assignedSlugs,
            $adminUser->isOwner()
        );
        $query->whereIn('slug', $allowedSlugs);

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
            $permissions = $this->includeStructuralAncestors($permissions);
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

    /**
     * Add filtered-out ancestors only as non-assignable tree containers.
     */
    protected function includeStructuralAncestors(Collection $permissions): Collection
    {
        if ($permissions->isEmpty()) {
            return $permissions;
        }

        $includedIds = array_fill_keys($permissions->pluck('id')->map(static function ($id) {
            return (int)$id;
        })->all(), true);
        $ancestorIds = [];
        $visitedIds = $includedIds;
        $parentIds = $permissions->pluck('parent_id')->filter()->map(static function ($id) {
            return (int)$id;
        })->unique()->values()->all();

        while ($parentIds) {
            $parentIds = array_values(array_filter($parentIds, static function ($id) use ($visitedIds) {
                return !isset($visitedIds[$id]);
            }));
            if (!$parentIds) {
                break;
            }

            $parents = AdminApiPermission::query()
                ->select(['id', 'name', 'slug', 'parent_id', 'order', 'path', 'created_at', 'updated_at'])
                ->whereIn('id', $parentIds)
                ->get();
            $nextParentIds = [];
            foreach ($parents as $parent) {
                $parentId = (int)$parent->id;
                $visitedIds[$parentId] = true;
                if (!isset($includedIds[$parentId])) {
                    $ancestorIds[] = $parentId;
                }
                if ($parent->parent_id) {
                    $nextParentIds[] = (int)$parent->parent_id;
                }
            }
            $parentIds = array_values(array_unique($nextParentIds));
        }

        $missingAncestorIds = array_values(array_unique($ancestorIds));
        if (!$missingAncestorIds) {
            $permissions->each(static function ($permission) {
                $permission->assignable = true;
            });

            return $permissions;
        }

        $ancestors = AdminApiPermission::query()
            ->select(['id', 'name', 'slug', 'parent_id', 'order', 'path', 'created_at', 'updated_at'])
            ->whereIn('id', $missingAncestorIds)
            ->orderBy('order')
            ->get();

        $permissions->each(static function ($permission) {
            $permission->assignable = true;
        });
        $ancestors->each(static function ($permission) {
            $permission->assignable = false;
        });

        return $ancestors->concat($permissions)->sortBy('order')->values();
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
