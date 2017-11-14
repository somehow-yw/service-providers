<?php
/**
 * Created by PhpStorm.
 * User: coderxiao
 * Date: 17-3-6
 * Time: 上午11:40
 */

namespace App\Repositories\Shop\Contracts;

use Illuminate\Support\Collection;

interface MainGoodsRepository
{
    /**
     * 获取商品分类
     *
     * @param     $marketId  integer 市场id
     * @param     $status    integer 状态 全部 0 已经加价 1 未加价 2
     *
     * @return array
     */
    public function getGoodsSorts($marketId, $status = 0, $series = 3);

    /**
     * 获取商品分类最后一级分类信息
     *
     * @param $Pid
     *
     * @return Collection
     */
    public function getGoodsSortInfo($Pid);

    /**
     * 获取商品详情
     *
     * @param $goodsId
     *
     * @param $select array
     *
     * @return Collection
     */
    public function getGoodsInfo($goodsId, $select = []);

    /**
     * 获取商品快照
     *
     * @param $goodsId
     *
     * @return Collection
     */
    public function getGoodsSnapShots($goodsId);
}