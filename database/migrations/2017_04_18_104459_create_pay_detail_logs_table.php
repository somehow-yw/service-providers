<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayDetailLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_detail_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('order_no', 64)->comment('商户订单号');
            $table->string('out_trade_no', 64)->comment('订单支付号');
            $table->text('request_detail')->comment('请求参数内容 JSON格式');
            $table->text('response_detail')->comment('支付平台 如微信返回的内容 JSON格式');
            $table->string('remark')->default('')->comment('备注');
            $table->timestamps();

            $table->index('order_no');
            $table->index('out_trade_no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pay_detail_logs');
    }
}
