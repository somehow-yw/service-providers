<?php

namespace App\Services;

use App\Models\Area;

class AreaService
{
    /**
     * 获取所有的省信息
     *
     * @return array
     */
    public function getProvince()
    {
        $provinces = Area::where('level', Area::LEVEL_PROVINCE)
                         ->get();

        $data = array_map(function ($key) {
            return self::formatForAdmin($key);
        }, $provinces->all());

        return $data;
    }

    /**
     * 获取某区域下的所有子区域
     *
     * @param $id integer
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function getChildren($id)
    {
        $cities = Area::where('pid', $id)
                      ->get();

        $data = array_map(function ($key) {
            return self::formatForAdmin($key);
        }, $cities->all());

        return $data;
    }

    /**
     * 格式化后端需要的数据
     *
     * @param Area $area
     *
     * @return array
     */
    protected function formatForAdmin(Area $area)
    {
        return [
            'id'    => $area->id,
            'name'  => $area->name,
            'level' => $area->level,
        ];
    }
}