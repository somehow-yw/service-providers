<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\AppException;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Zdp\Main\Data\Services\Goods\ShoppingCartService;

class Cart extends Controller
{
    public function upload(
        Request $request,
        \Maatwebsite\Excel\Excel $excel,
        ShoppingCartService $service
    ) {
        if (!$request->hasFile('file')) {
            abort(403, '未上传文件');
        }

        $sp = getServiceProvider();

        if (empty($sp)) {
            abort(403, '找不到服务商');
        }

        if ($sp->zdp_user_id != 5908) {
            abort(403);
        }

        $errors = [];

        $excel->load(
            $request->file('file')->getRealPath(),
            function ($reader) use ($service, &$errors, $sp) {
                $reader->noHeading();
                foreach ($reader->get() as $row) {
                    $goodsId = $row->get(0);
                    $nums = $row->get(1);

                    if ($nums <= 0) {
                        $errors[] =
                            "添加 {$nums} 个单位的商品 #{$goodsId} 失败: 不能添加 0 个商品到购物车";
                        continue;
                    }

                    try {
                        // !!!这里有个神逻辑
                        // 直接加入杨大爷的购物车，
                        $service->addToCart($goodsId, $nums, '18171');
                    } catch (\Exception $e) {
                        $errors[] = "添加 {$nums} 个单位的商品 #{$goodsId} 失败: " .
                                    $e->getMessage();
                    }
                }
            }
        );

        dd($errors);
    }

    public function download(Request $request, \Maatwebsite\Excel\Excel $excel)
    {
        $this->validate($request, [
            'start' => 'date_format:Y-m-d',
            'end'   => 'date_format:Y-m-d',
        ]);

        $sp = getServiceProvider();

        if (empty($sp)) {
            abort(403, '找不到服务商');
        }

        if ($sp->zdp_user_id != 5908) {
            abort(403);
        }

        $start = $request->get(
            'start',
            Carbon::yesterday()->format('Y-m-d')
        );
        $end = $request->get(
            'end',
            Carbon::today()->format('Y-m-d')
        );

        $start = Carbon::createFromFormat('Y-m-d', $start)->startOfDay();
        $end = Carbon::createFromFormat('Y-m-d', $end)->endOfDay();

        $excel->create(
            '进货记录' . Carbon::yesterday()->format('y年m月d日') . '至' .
            Carbon::now()->format('y年m月d日 H时i分'),
            function ($excel) use ($start, $end) {
                $excel->setTitle('进货记录');
                $excel->setCreator('陈弘瑀')
                      ->setCompany('找冻品网');
                $excel->setDescription(Carbon::yesterday()->format('y年m月d日') .
                                       ' 至 ' .
                                       Carbon::now()->format('y年m月d日H时i分') .
                                       '的进货记录');

                $data = \DB::connection('main_mysql')->select('SELECT
  c.addtime        AS `下单时间`,
  s.dianPuName     AS `卖家`,
  g.gname          AS `商品名`,
  g.id             AS `商品ID`,
  c.buy_num        AS `数量`,
  c.good_new_price AS `商品单价`,
  c.count_price    AS `价格`,
  CASE c.good_act
  WHEN 0
    THEN \'新订单\'
  WHEN 1
    THEN \'已电联\'
  WHEN 2
    THEN \'已付款\'
  WHEN 3
    THEN \'已发货\'
  WHEN 4
    THEN \'已收货\'
  WHEN 5
    THEN \'已取消\'
  WHEN 6
    THEN \'已取消\'
  WHEN 7
    THEN \'提现中\'
  WHEN 8
    THEN \'已提现\'
  WHEN 9
    THEN \'已评价\'
  WHEN 20
    THEN \'已取消\'
  WHEN 101
    THEN \'待收款\'
  WHEN 102
    THEN \'退款中\'
  WHEN 30
    THEN \'已退款\'
  WHEN 40
    THEN \'已退款\'
  END              AS `订单状态`
FROM dp_cart_info AS c
  JOIN dp_opder_form AS o
    ON o.order_code = c.coid AND
       o.addtime >= ? AND o.addtime <= ?
  JOIN dp_shopinfo AS s ON s.shopId = o.shopid
  JOIN dp_goods_info AS g ON g.id = c.goodid
WHERE c.uid = 18171 AND c.good_act > 0
ORDER BY `下单时间`', [$start, $end]);

                $data = array_map(function ($a) {
                    return (array)$a;
                }, $data);

                $excel->sheet('进货记录', function ($sheet) use ($data) {
                    $sheet->setAutoSize(true);
                    $sheet->fromArray($data);
                });
            }
        )->download('xlsx');
    }
}