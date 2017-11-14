<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Zdp\ServiceProvider\Data\Services\HomeItem;
use Zdp\ServiceProvider\Data\Models\HomeItem as HomeItemModel;

class Home extends Controller
{
    /**
     * 获取服务商是否开启首页
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function isEnable()
    {
        $sp = getServiceProvider();

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $sp->enable_custom_homepage,
        ]);
    }

    /**
     * 开启首页
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function enable(Request $request)
    {
        $this->validate(
            $request,
            [
                'enable' => 'required|in:0,1',
            ]
        );

        $enabled = $request->input('enable');

        $sp = getServiceProvider();
        $sp->enable_custom_homepage = $enabled;

        $sp->saveOrFail();

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
        ]);
    }

    /**
     * 获取热门分类
     *
     * @param Request  $request
     * @param HomeItem $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function hotGoodsCategories(HomeItem $service)
    {
        $items = $service
            ->getHomeItemByType(HomeItemModel::TYPE_HOT_GOODS_CATEGORY);

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $service->formatForAdmin($items),
        ]);
    }

    /**
     * 重设热销分类
     *
     * @param Request  $request
     * @param HomeItem $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetHotGoodsCategory(Request $request, HomeItem $service)
    {
        $this->validate(
            $request,
            [
                'categories' => 'array|max:8',
            ]
        );

        $service->resetHomeItemByType(
            $request->input('categories', []),
            HomeItemModel::TYPE_HOT_GOODS_CATEGORY
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
        ]);
    }

    /**
     * 获取热门品牌
     *
     * @param Request  $request
     * @param HomeItem $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function hotGoodsBrand(HomeItem $service)
    {
        $items = $service
            ->getHomeItemByType(HomeItemModel::TYPE_HOT_GOODS_BRAND);

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => $service->formatForAdmin($items),
        ]);
    }

    /**
     * 重设热销品牌
     *
     * @param Request  $request
     * @param HomeItem $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetHotGoodsBrand(Request $request, HomeItem $service)
    {
        $this->validate(
            $request,
            [
                'brands' => 'array|max:8',
            ]
        );

        $service->resetHomeItemByType(
            $request->input('brands', []),
            HomeItemModel::TYPE_HOT_GOODS_BRAND
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
        ]);
    }

    /**
     * 获取推荐商品
     *
     * @param Request  $request
     * @param HomeItem $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function hotGoods(Request $request, HomeItem $service)
    {
        $this->validate(
            $request,
            [
                'page' => 'integer',
                'size' => 'integer|max:100',
            ]
        );
        $items = $service
            ->getHomeItemByType(
                HomeItemModel::TYPE_HOT_GOODS,
                $request->input('page', 1),
                $request->input('size', 20)
            );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
            'data'    => [
                'goods'     => $service
                    ->formatForAdmin($items, HomeItemModel::TYPE_HOT_GOODS),
                'total'     => $items->total(),
                'last_page' => $items->lastPage(),
            ],
        ]);
    }

    /**
     * 重设推荐商品
     *
     * @param Request  $request
     * @param HomeItem $service
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetHotGoods(Request $request, HomeItem $service)
    {
        $this->validate(
            $request,
            [
                'goods' => 'array|max:80',
            ]
        );

        $service->resetHomeItemByType(
            $request->input('goods', []),
            HomeItemModel::TYPE_HOT_GOODS
        );

        return response()->json([
            'code'    => 0,
            'message' => 'OK',
        ]);
    }

}