<?php

namespace App\Services\WebAdmin;

use App\Models\WebAdminUser;
use Carbon\Carbon;
use Zdp\ServiceProvider\Data\Models\Order;
use Zdp\ServiceProvider\Data\Models\User;

class Statics
{
    const ACCEPT_FIELDS = [
        'ytd_new_usr',
        'ytd_money',
        'total_usr',
        'total_money',
    ];

    public function index($fields = [])
    {
        $fields = array_intersect($fields, self::ACCEPT_FIELDS);

        if (empty($fields)) {
            $fields = self::ACCEPT_FIELDS;
        }

        $result = [];

        foreach ($fields as $field) {
            $result[$field] = call_user_func([$this, $field]);
        }

        return $result;
    }

    /**
     * 昨日新增用户
     *
     * @return int
     */
    public function ytd_new_usr()
    {
        $admin = \App::make(WebAdminUser::class);
        $spId = $admin->getSp()->zdp_user_id;

        return User::query()
                   ->where('sp_id', $spId)
                   ->where('created_at', '>=', Carbon::yesterday())
                   ->where('created_at', '<', Carbon::today())
                   ->count();
    }

    /**
     * 昨日确认收入
     *
     * @return mixed
     */
    public function ytd_money()
    {
        $admin = \App::make(WebAdminUser::class);
        $spId = $admin->getSp()->zdp_user_id;

        return Order::query()
                    ->where('sp_id', $spId)
                    ->where('created_at', '>=', Carbon::yesterday())
                    ->where('created_at', '<', Carbon::today())
                    ->whereIn('status', Order::STATUS_NORMAL)
                    ->sum('order_amount');
    }

    /**
     * 总注册用户
     *
     * @return int
     */
    public function total_usr()
    {
        $admin = \App::make(WebAdminUser::class);
        $spId = $admin->getSp()->zdp_user_id;

        return User::query()
                   ->where('sp_id', $spId)
                   ->count();
    }

    /**
     * 总销售收入
     *
     * @return mixed
     */
    public function total_money()
    {
        $admin = \App::make(WebAdminUser::class);
        $spId = $admin->getSp()->zdp_user_id;

        return Order::query()
                    ->where('sp_id', $spId)
                    ->whereIn('status', Order::STATUS_NORMAL)
                    ->sum('order_amount');
    }
}