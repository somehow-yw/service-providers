<?php

namespace App\Services;

use Zdp\ServiceProvider\Data\Models\Markup;

class PriceService
{
    /**
     * 服务商加价
     *
     * @param array $sortIds
     * @param       $increment
     */
    public function rise(array $sortIds, $increment, $type)
    {
        $sid = getServiceProvider()->zdp_user_id;

        if ($type == Markup::MARK_UP_TYPE_PER) {
            $increment = $increment / 100 + 1;
        }

        array_map(function ($id) use ($sid, $increment, $type) {
            Markup::updateOrCreate([
                'sp_id'   => $sid,
                'sort_id' => $id,
            ], [
                'increase' => $increment,
                'type'     => $type,
            ]);
        }, $sortIds);
    }
}