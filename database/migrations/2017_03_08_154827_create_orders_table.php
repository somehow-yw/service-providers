<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('order_no', 32)->unique()->comment('订单编号');
            $table->integer('sp_id')->unsigned()->comment('服务商在找冻品网的用户id');
            $table->integer('user_id')->unsigned()->comment('会员ID');
            $table->smallInteger('goods_num')->unsigned()->comment('购买的商品个数');
            $table->smallInteger('buy_count')->unsigned()->comment('购买总数');
            $table->decimal('order_amount', 11, 2)->comment('订单金额');
            $table->tinyInteger('payment')->unsigned()->comment('支付方式，如：货到付款 在MODEL中定义');
            $table->tinyInteger('delivery')->unsigned()->comment('商品交付方式，如：配送到店 在MODEL中定义');
            $table->text('consignee_info')->comment('收货人信息');
            $table->tinyInteger('status')->unsigned()->default(1)->comment('订单状态 如：1=新订单(已做默认值)等在MODEL中定义');
            $table->timestamps();
            $table->softDeletes();

            $table->index('sp_id');
            $table->index('user_id');
            $table->index('payment');
            $table->index('delivery');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
