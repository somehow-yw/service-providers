<?php

namespace App\Http\Controllers\Customer;

use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use App\Services\GoodsService;
use Illuminate\Http\Request;
use Zdp\Main\Data\Models\DpGoodsInfo;
use Zdp\Search\Services\SpSearch as ElasticService;
use Zdp\ServiceProvider\Data\Services\GoodsCategoryBrand;
use Zdp\ServiceProvider\Data\Services\UserCollection;

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
    public function getGoodsInfo(
        $id,
        GoodsService $service,
        UserCollection $collectionService,
        GoodsCategoryBrand $blacklistService
    ) {
        if (!DpGoodsInfo::find($id)) {
            throw new AppException("商品不存在");
        }

        $goodsInfo = $service->getGoodsInfo($id);
        if (!empty($goodsInfo)) {
            $goodsInfo['collected'] = $collectionService->isCollected($id);
        }

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $goodsInfo,
        ]);
    }

    /**
     * 获取商品分类
     *
     * @param GoodsService $service
     *
     * @return \Illuminate\Http\JsonResponse
     * @internal param Request $request
     */
    public function getSorts(GoodsService $service)
    {
        $sorts = $service->getSorts(
            0,
            0
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $sorts,
        ]);
    }

    /**
     * 获取收藏商品列表
     *
     * @param UserCollection $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCollections(UserCollection $service)
    {
        $collections = $service->getAll();

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $service->formatListForCustomer($collections),
        ]);
    }

    /**
     * 添加商品收藏
     *
     * @param Request        $request
     * @param UserCollection $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addCollections(Request $request, UserCollection $service)
    {
        $this->validate(
            $request,
            [
                'ids' => 'required|array',
            ]
        );

        $service->add($request->input('ids'));

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
        ]);
    }

    /**
     * 删除商品收藏
     *
     * @param Request        $request
     * @param UserCollection $service
     */
    public function delCollections(Request $request, UserCollection $service)
    {
        $this->validate(
            $request,
            [
                'ids' => 'required|array',
            ]
        );

        $service->del($request->input('ids'));

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
        ]);
    }
}
