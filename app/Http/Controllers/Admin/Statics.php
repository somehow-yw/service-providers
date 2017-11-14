<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebAdminUser;
use Illuminate\Http\Request;
use Zdp\BI\Services\ServiceProvider\StatsCustomer;

class Statics extends Controller
{
    /**
     * 获取首页统计字段
     *
     * @param Request                        $request
     * @param WebAdminUser                   $user
     * @param \App\Services\WebAdmin\Statics $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(
        Request $request,
        WebAdminUser $user,
        \App\Services\WebAdmin\Statics $service
    ) {
        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $service->index(explode(
                ',',
                $request->input('fields')
            )),
        ]);
    }

    /**
     * 获取客户增长曲线
     *
     * @param Request       $request
     * @param StatsCustomer $service
     * @param WebAdminUser  $user
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function custom(
        Request $request,
        StatsCustomer $service,
        WebAdminUser $user
    ) {
        $sp = $user->getSp();
        $sp_id = $sp->zdp_user_id;

        $data = $service->customerStats(
            null,
            null,
            $request->input('time'),
            ['sp_id' => [$sp_id]],
            null
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $data,
        ]);
    }
}