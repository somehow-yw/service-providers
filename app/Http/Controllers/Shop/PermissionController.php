<?php

namespace App\Http\Controllers\Shop;

use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\SpMemberPermission;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * 获取当前用户的权限列表
     *
     * @param SpMemberPermission $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function current(SpMemberPermission $service)
    {
        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $service->getCurrentPermissions(),
        ]);
    }

    /**
     * 获取某个用户的权限
     *
     * @param Request            $request
     * @param SpMemberPermission $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function member(Request $request, SpMemberPermission $service)
    {
        $this->validate(
            $request,
            [
                'wechat_openid' => 'required|exists:sp_member,wechat_openid',
            ]
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $service->getMemberPermissions($request->input('wechat_openid')),
        ]);
    }

    /**
     * 编辑某个用户的权限
     *
     * @param Request            $request
     * @param SpMemberPermission $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Request $request, SpMemberPermission $service)
    {
        $this->validate(
            $request,
            [
                'wechat_openid' => 'required|exists:sp_member,wechat_openid',
                'permission'    => 'required|string|max:16',
                'enabled'       => 'required|in:0,1',
            ]
        );

        $service->setPermissions(
            $request->input('wechat_openid'),
            $request->input('permission'),
            $request->input('enabled')
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
        ]);
    }
}
