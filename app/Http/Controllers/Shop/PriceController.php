<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\PriceService;
use Illuminate\Http\Request;
use Zdp\ServiceProvider\Data\Services\GoodsCategoryBrand;

class PriceController extends Controller
{
    protected $price;

    public function __construct(PriceService $priceService)
    {
        $this->price = $priceService;
    }

    /**
     * 服务商按照商品分类进行加价
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function rise(Request $request, GoodsCategoryBrand $service)
    {
        $this->validate(
            $request,
            [
                'sort_id'    => 'required|exists:main_mysql.dp_goods_types,id,series,4',
                'brand_ids'  => 'array',
                'percentage' => 'required|numeric',
            ],
            [
                'sort_ids.required' => '分类id必须有',
                'sort_ids.array'    => '分类id为数组',

                'increase.required' => '加价金额必须有',
                'increase.numeric'  => '加价金额为数字',
            ]
        );

        $service->markUp(
            $request->input('sort_id'),
            $request->input('brand_ids'),
            $request->input('percentage')
        );

        return response()->json([
            'code'    => 0,
            'message' => '改价成功',
        ]);
    }
}