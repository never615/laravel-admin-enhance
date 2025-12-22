<?php
/*
 * Copyright (c) 2025. Mallto.Co.Ltd.<mall-to.com> All rights reserved.
 */

namespace Mallto\Admin\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Mallto\Admin\Domain\User\AdminUserUsecase;
use Mallto\Tool\Exception\PermissionDeniedException;

class AdminUserProfileController extends Controller
{
    public function show(): JsonResponse
    {
        $adminUser = Auth::guard('admin_api')->user();
        if (!$adminUser) {
            throw new PermissionDeniedException('Not authenticated');
        }

        if ($adminUser->status === 'forbidden') {
            throw new PermissionDeniedException('Account disabled');
        }

        $adminUserUsecase = app(AdminUserUsecase::class);

        return response()->json($adminUserUsecase->getReturnUserInfo($adminUser, true));
    }
}

