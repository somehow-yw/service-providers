<?php

namespace App\Http\Controllers\Shop;

use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    /**
     * 获取当前店铺的客户列表
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function customers(Request $request)
    {
        $this->validate($request, [
            's' => 'int',
            'p' => 'int',
        ]);

        $shop = getServiceProvider();

        $users = User::query()
            ->where('sp_id', $shop->zdp_user_id)
            ->paginate(
                $request->input('s', 10),
                ['user_name', 'shop_name', 'mobile_phone'],
                null,
                $request->input('p', 1)
            );

        return response()->json(
            [
                'code'    => 0,
                'message' => 'OK',
                'data'    => $users,
            ]
        );
    }


    /**
     * 获取客户信息
     *
     * @param $id integer 客户id
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function getCustomer($id)
    {
        $shop = getServiceProvider();

        /** @var User $user */
        $user = User::where('sp_id', $shop->zdp_user_id)
            ->where('id', $id)
            ->first();

        if (empty($user)) {
            throw new AppException("该用户不存在");
        }

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => [
                'shop_name'    => $user->shop_name,
                'user_name'    => $user->user_name,
                'mobile'       => $user->mobile_phone,
                'shop_address' => $user->full_address,
            ],
        ]);
    }
}
