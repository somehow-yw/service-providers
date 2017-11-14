<?php

namespace App\Http\Controllers\Shop;

use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use App\Services\GoodsService;
use Illuminate\Http\Request;
use Zdp\Main\Data\Models\DpGoodsInfo;
use Zdp\Search\Services\SpSearch as ElasticService;
use Zdp\ServiceProvider\Data\Services\GoodsCategoryBrand;
use Zdp\ServiceProvider\Data\Services\GoodsStick;

class GoodsController extends Controller
{

    /**
     * 获取筛选项
     *
     * @param Request        $request
     * @param ElasticService $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function filters(
        Request $request,
        ElasticService $service
    ) {
        $this->validate(
            $request,
            [
                'area_id'   => 'integer',
                'type_ids'  => 'array',
                'brand_ids' => 'array',
                'xinghaos'  => 'array',
                'halal'     => 'boolean',
                'select'    => 'array',
            ],
            [
                'area_id.required' => '必须指定大区ID',
                'area_id.integer'  => '大区ID 必须为整数',
                'type_ids.array'   => '商品类型 必须为数组',
                'brand_ids.array'  => '商品品牌 必须为数组',
                'xinghaos.array'   => '商品型号 必须为数组',
                'halal.boolean'    => '是否清真 必须为布尔值',
                'select.array'     => '需要数据 必须为数组',
            ]
        );

        $market_ids = explode(',', getServiceProvider()->market_ids);

        if (empty($market_ids)) {
            $data = [];
        } else {
            $data = $service->goodsFilters(
                $request->input('area_id'),
                $request->input('search'),
                $request->input('type_ids'),
                $request->input('brand_ids'),
                $request->input('xinghaos'),
                $request->input('halal'),
                $market_ids,
                null,
                $request->input('select')
            );
        }

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $data,
        ]);
    }

    /**
     * 获取商品列表
     *
     * @param Request        $request
     * @param ElasticService $service
     *
     * @return mixed
     */
    public function goods(
        Request $request,
        ElasticService $service
    ) {
        $this->validate(
            $request,
            [
                'area_id'   => 'integer',
                'type_ids'  => 'array',
                'brand_ids' => 'array',
                'xinghaos'  => 'array',
                'halal'     => 'boolean',
                'order'     => 'array',
                'order.0'   => 'integer',
                'order.1'   => 'boolean',
                'page'      => 'integer',
                'size'      => 'integer',
            ],
            [
                'area_id.required' => '必须指定大区ID',
                'area_id.integer'  => '大区ID 必须为整数',
                'type_ids.array'   => '商品类型 必须为数组',
                'brand_ids.array'  => '商品品牌 必须为数组',
                'xinghaos.array'   => '商品型号 必须为数组',
                'halal.boolean'    => '是否清真 必须为布尔值',
                'order.array'      => '排序方式 必须维数组',
                'order.0.integer'  => '排序类型 必须为数字 不填/0: 默认 1: 好评度 2: 销量 3: 价格',
                'order.1.boolean'  => '排序顺序 必须为布尔值 true: 正序 false: 逆序',
                'page.integer'     => '页码 必须为整数',
                'size.integer'     => '页大小 必须为整数',
            ]
        );

        $market_ids = explode(',', getServiceProvider()->market_ids);

        if (empty($market_ids)) {
            $data = [
                'goods'    => [],
                'page_all' => 0,
                'page'     => (int)$request->input('page', 1),
                'size'     => (int)$request->input('size', 10),
                'total'    => 0,
            ];
        } else {
            $service->showOriginPrice = true;
            $data = $service->goods(
                $request->input('area_id'),
                $request->input('search'),
                $request->input('type_ids'),
                $request->input('brand_ids'),
                $request->input('xinghaos'),
                $request->input('halal'),
                $market_ids,
                null,
                $request->input('order'),
                $request->input('page', 1),
                $request->input('size', 10),
                $request->input('user_id')
            );
        }

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $data,
        ]);
    }

    /**
     * 获取商品详情
     *
     * @param              $id integer 商品id
     * @param GoodsService $service
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws AppException
     */
    public function getGoodsInfo($id, GoodsService $service)
    {
        if (!DpGoodsInfo::find($id)) {
            throw new AppException("商品不存在");
        }

        $goodsInfo = $service->getGoodsInfo($id, true);

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $goodsInfo,
        ]);
    }

    /**
     * 获取商品屏蔽列表
     *
     * @param Request            $request
     * @param GoodsCategoryBrand $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBlacklist(Request $request, GoodsCategoryBrand $service)
    {
        $this->validate(
            $request,
            [
                'sort_id' => 'required|exists:main_mysql.dp_goods_types,id,series,3',
            ],
            [
                'sort_id.required' => '三级分类ID未传入',
                'sort_id.exists'   => '三级分类ID不存在',
            ]
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $service->getDisplayList(
                $request->input('sort_id'),
                \Zdp\ServiceProvider\Data\Models\GoodsCategoryBrand::DISPLAY_BLACKLIST
            ),
        ]);
    }

    /**
     * 添加商品屏蔽项
     *
     * @param Request            $request
     * @param GoodsCategoryBrand $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addBlacklist(Request $request, GoodsCategoryBrand $service)
    {
        $this->validate(
            $request,
            [
                'sort_id'   => 'required|exists:main_mysql.dp_goods_types,id,series,4',
                'brand_ids' => 'required|array',
            ],
            [
                'sort_id.required' => '四级分类ID未传入',
                'sort_id.exists'   => '四级分类ID不存在',
            ]
        );

        $service->addBlacklist(
            $request->input('sort_id'),
            $request->input('brand_ids')
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
        ]);
    }

    /**
     * 删除商品屏蔽项
     *
     * @param Request            $request
     * @param GoodsCategoryBrand $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeBlacklist(
        Request $request,
        GoodsCategoryBrand $service
    ) {
        $this->validate(
            $request,
            [
                'sort_id'   => 'required|exists:main_mysql.dp_goods_types,id,series,4',
                'brand_ids' => 'required|array',
            ],
            [
                'sort_id.required' => '四级分类ID未传入',
                'sort_id.exists'   => '四级分类ID不存在',
            ]
        );

        $service->removeBlacklist(
            $request->input('sort_id'),
            $request->input('brand_ids')
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
        ]);
    }

    /**
     * 获取置顶列表
     *
     * @param Request            $request
     * @param GoodsCategoryBrand $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSticks(Request $request, GoodsCategoryBrand $service)
    {
        $this->validate(
            $request,
            [
                'sort_id' => 'required|exists:main_mysql.dp_goods_types,id,series,3',
            ],
            [
                'sort_id.required' => '三级分类ID未传入',
                'sort_id.exists'   => '三级分类ID不存在',
            ]
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $service->getDisplayList(
                $request->input('sort_id'),
                \Zdp\ServiceProvider\Data\Models\GoodsCategoryBrand::DISPLAY_STICK
            ),
        ]);
    }

    /**
     * 重设置顶列表
     *
     * @param Request            $request
     * @param GoodsCategoryBrand $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetSticks(Request $request, GoodsCategoryBrand $service)
    {
        $this->validate(
            $request,
            [
                'sort_id'   => 'required|exists:main_mysql.dp_goods_types,id,series,4',
                'brand_ids' => 'array',
            ],
            [
                'sort_id.required' => '四级分类ID未传入',
                'sort_id.exists'   => '四级分类ID不存在',
            ]
        );

        $service->resetSticks(
            $request->input('sort_id'),
            $request->input('brand_ids')
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
        ]);
    }
}
