<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17-3-6
 * Time: 上午11:40
 */

namespace App\Repositories\Shop;

use App\Repositories\Shop\Contracts\MainGoodsRepository as Contract;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Zdp\Main\Data\Models\DpGoodsInfo;
use Zdp\Main\Data\Models\DpGoodsType;
use DB;
use Zdp\ServiceProvider\Data\Models\Markup;

Class MainGoodsRepository implements Contract
{
    const TYPE_STR = 'substring_index(substring_index(sortid,\',\',3), \',\', -1)';

    /**
     * @inheritDoc
     */
    public function getGoodsSorts($marketId, $status = 0, $series = 3)
    {
        /** @var Builder $baseQuery */
        $baseQuery = DpGoodsType
            ::select([
                'id',
                'sort_name',
                'fid',
                'nodeid',
                'pic_url',
                'series',
            ])
            ->orderBy('series', 'asc')
            ->orderBy('sort_value', 'asc');

        if ($status && $series >= 3) {
            $query = clone  $baseQuery;

            $series4 = $query->where('series', 4)->get();

            $lastTypeIds = $series4->pluck('id')->toArray();

            $serviceProvider = getServiceProvider();

            /** @var Collection $markUps */
            $markUps = Markup::select('sort_id')
                             ->where('sp_id', $serviceProvider->zdp_user_id)
                             ->whereIn('sort_id', $lastTypeIds)
                             ->get();

            $markUpIds = $markUps->pluck('sort_id')->toArray();

            if ($status == 1) {
                $query = clone $baseQuery;

                $markUpSeries4 = $query->whereIn('id', $markUpIds)->get();
                /** @var array $series4Fids 所有加价的父级id */
                $series4Fids =
                    array_unique($markUpSeries4->pluck('fid')->toArray());

                $query = clone  $baseQuery;
                // 这里应该是所有第一二级 子类有加价的第三级分类 所有加价的第四级分类
                $goodsType = $query->whereIn('id', $markUpIds)
                                   ->orWhereIn('series', [1, 2])
                                   ->orWhere(function ($q) use (
                                       $series4Fids
                                   ) {
                                       $q->where('series', 3)
                                         ->whereIn('id', $series4Fids);
                                   })
                                   ->get();

            } elseif ($status == 2) {
                $query = clone  $baseQuery;
                $goodsType = $query->whereNotIn('id', $markUpIds)
                                   ->where('series', '<=', $series)
                                   ->get();
            }
        } else {
            $goodsType = $baseQuery->where('series', '<=', $series)->get();
        }

        $goodsTypeArr = [];
        /** @var Collection $goodsType */
        if (!$goodsType->isEmpty()) {
            foreach ($goodsType as $item) {
                $nodeIds = $item->nodeid;
                $keys = str_replace(',', '.', $nodeIds);
                $varTempArr = [
                    'type_id'      => $item->id,
                    'type_name'    => $item->sort_name,
                    'type_pic_url' => $item->pic_url,
                ];
                array_set($goodsTypeArr, $keys, $varTempArr);
            }
        }

        return $goodsTypeArr;
    }

    /**
     * @inheritDoc
     */
    public function getGoodsSortInfo($Pid)
    {
        return DpGoodsType::select(
            'dp_goods_types.id',
            'dp_goods_types.sort_name',
            'dp_goods_types.sale_goods_num',
            'dp_goods_types.pic_url as sort_image',
            \DB::raw(
                'SUM(s.total_sales) as total_sales'
            )
        )
                          ->leftjoin(
                              'dp_goods_info as g',
                              'dp_goods_types.id',
                              '=',
                              DB::raw(self::TYPE_STR)
                          )
                          ->leftjoin('dp_goods_total_sales_stats as s', 'g.id',
                              '=', 's.goods_id')
                          ->where('dp_goods_types.fid', $Pid)
                          ->groupBy('dp_goods_types.id')
                          ->get();
    }

    /**
     * @inheritDoc
     */
    public function getGoodsInfo($goodsId, $select = [])
    {
        if (empty($select)) {
            $select = [
                /*
                 * dp_goods_info
                 */
                'dp_goods_info.id',
                'dp_goods_info.id as goods_id',
                'dp_goods_info.gname as goods_name',
                'dp_goods_info.goods_type_id',
                'dp_goods_info.goods_title',
                'dp_goods_info.brand_id as goods_brand_id',
                'dp_goods_info.brand as goods_brand',
                'dp_goods_info.origin as goods_origin',
                'dp_goods_info.jianjie as goods_description',
                'dp_goods_info.picnum as goods_image_count',
                'dp_goods_info.inspection_report as inspection_report',
                'dp_goods_info.on_sale',
                'dp_goods_info.shenghe_act as goods_status',
                'dp_goods_info.halal',
                /*
                 * dp_goods_basic_attributes
                 */
                'dp_goods_basic_attributes.xinghao as goods_xinghao',
                'dp_goods_basic_attributes.guigei as goods_guigei',
                'dp_goods_basic_attributes.rough_weight as goods_rough_weight',
                'dp_goods_basic_attributes.meat_weight as goods_meat_weight',
                'dp_goods_basic_attributes.net_weight as goods_net_weight',
                'dp_goods_basic_attributes.goods_price',
                'dp_goods_basic_attributes.opder_num as goods_sell_num',
                'dp_goods_basic_attributes.fromsell_num as goods_start_num',
                'dp_goods_basic_attributes.tag as type',
                'dp_goods_basic_attributes.inventory as goods_inventory',
                'dp_goods_basic_attributes.auto_soldout_time as end_time',
                'dp_goods_basic_attributes.meter_unit as goods_unit',
                'dp_goods_basic_attributes.auto_soldout_time',
            ];
        }

        $goodsInfo = DpGoodsInfo::with(
            [
                'goodsPicture' => function ($query) {
                    $query->select('goodsid', 'ypic_path as goods_image');
                },
                //暂时不考虑价格体系
                //                'priceRule'    => function ($query) {
                //                    $query->select(
                //                        [
                //                            'goods_id',
                //                            'buy_num',
                //                            'price_rule_id',
                //                            'preferential',
                //                        ]
                //                    )->orderBy('buy_num');
                //                },
            ]
        )
                                ->join('dp_goods_basic_attributes',
                                    'dp_goods_basic_attributes.goodsid', '=',
                                    'dp_goods_info.id')
                                ->where('dp_goods_info.shenghe_act',
                                    DpGoodsInfo::STATUS_NORMAL)
                                ->where('dp_goods_info.id', $goodsId)
                                ->select($select)
                                ->first();

        return $goodsInfo;
    }

    /**
     * @inheritDoc
     */
    public function getGoodsSnapShots($goodsId)
    {
        $query = DpGoodsInfo::with(
            'goodsAttribute',
            'specialAttribute',
            'goodsPicture',
            'goodsInspectionReport'
        );
        if (is_array($goodsId)) {
            $query = $query->whereIn('id', $goodsId);
        } else {
            $query = $query->where('id', $goodsId);
        }

        return $query->get();
    }
}