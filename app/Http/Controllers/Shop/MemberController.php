<?php

namespace App\Http\Controllers\Shop;

use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Zdp\ServiceProvider\Data\Models\SpMember;
use Zdp\ServiceProvider\Data\Models\SpMemberInvite;

class MemberController extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function share(Request $request)
    {
        $isOwner = isSpOwner();

        if (!$isOwner) {
            throw new AppException('没有权限');
        }

        $hash = SpMemberInvite::generateHash(getServiceProvider());

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => [
                'url' => route('new-shop-member', ['hash' => $hash]),
            ],
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function all()
    {
        $isOwner = isSpOwner();

        if (!$isOwner) {
            throw new AppException('没有权限');
        }

        $sp = getServiceProvider();

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $sp->members,
        ]);
    }

    public function hasRight()
    {
        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => (boolean)isSpOwner(),
        ]);
    }

    public function delete(Request $request)
    {
        $isOwner = isSpOwner();

        if (!$isOwner) {
            throw new AppException('没有权限');
        }

        $sp = getServiceProvider();

        SpMember::where('sp_id', $sp->zdp_user_id)
                ->where('wechat_openid', $request->input('wechat_openid'))
                ->delete();

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
        ]);
    }
}