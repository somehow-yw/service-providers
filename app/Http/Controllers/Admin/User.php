<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use App\Models\WebAdminUser;
use Illuminate\Http\Request;
use Zdp\ServiceProvider\Data\Models\ServiceProvider;

class User extends Controller
{
    /**
     * 登陆
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function login(Request $request)
    {
        $this->validate(
            $request,
            [
                'token' => 'required',
            ],
            [
                'token.required' => '未获取到登陆码',
            ]
        );

        WebAdminUser::login($request->input('token'));

        if ($request->ajax()) {
            return response()->json([
                'code'    => 0,
                'message' => 'OK',
            ]);
        } else {
            return redirect(config('admin.urls.index'));
        }
    }

    /**
     * 登出
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        WebAdminUser::logout();

        if ($request->ajax()) {
            return response()->json([
                'code'    => 0,
                'message' => 'OK',
            ]);
        } else {
            return redirect(config('admin.urls.login'));
        }
    }

    /**
     * 获取用户信息
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInfo(Request $request, WebAdminUser $user)
    {
        $sp = $user->getSp();

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => ServiceProvider::formatForAdmin($sp,
                ServiceProvider::FORMAT_WEB_ADMIN),
        ]);
    }

    /**
     * 更新用户信息
     *
     * @param Request      $request
     * @param WebAdminUser $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateInfo(Request $request, WebAdminUser $user)
    {
        $rule = [
            'shop_name'       => 'required|string',
            'mobile'          => 'required|string',
            'province_id'     => 'required|integer',
            'city_id'         => 'required|integer',
            'county_id'       => 'required|integer',
            'address'         => 'required|string',
            'avatar'          => 'string',
            'introduction'    => 'string|max:100',
            'delivery_remark' => 'string|max:40',
        ];

        $this->validate(
            $request,
            $rule
        );

        $sp = $user->getSp();

        $basicKeys = [
            'shop_name',
            'mobile',
            'province_id',
            'city_id',
            'county_id',
            'address',
        ];

        if (!$sp->update($request->only($basicKeys))) {
            throw new AppException('保存基本信息失败');
        }

        $addition = $request->only([
            'introduction',
            'delivery_remark',
        ]);

        if (!empty($request->avatar)) {
            $base64_str = substr(
                $request->avatar,
                strpos($request->avatar, ",") + 1
            );
            $disk = \Storage::disk('aliyun');
            $path = 'image/' . $sp->zdp_user_id . uniqid();
            $name = $disk->put($path, base64_decode($base64_str));

            if (!$name) {
                throw new AppException('图片上传失败');
            }

            $addition['avatar'] = $path;
        }

        if (!empty($addition)) {
            $sp->info()->updateOrCreate([
                'sp_id' => $sp->zdp_user_id,
            ], $addition);
        }

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => ServiceProvider::formatForAdmin(
                $sp,
                ServiceProvider::FORMAT_WEB_ADMIN
            ),
        ]);
    }
}