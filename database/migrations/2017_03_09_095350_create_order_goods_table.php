<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_goods', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->unsigned()->comment('订单ID');
            $table->integer('goods_id')->unsigned()->comment('商品ID');
            $table->text('goods_info')->comment('商品信息');
            $table->tinyInteger('status')->unsigned()->default(1)->comment('状态,如：1=未进货(已做默认值) 2=已进货 3=已退货等');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['order_id','status']);
            $table->index('status');
            $table->index('goods_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_goods');
    }
}
