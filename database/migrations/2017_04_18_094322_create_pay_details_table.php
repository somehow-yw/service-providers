<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePayDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pay_details', function (Blueprint $table) {
            $table->increments('id');
            $table->string('order_no', 64)->comment('商户订单号');
            $table->string('out_trade_no', 64)->comment('订单支付号');
            $table->string('prepay_id', 64)->default('')->comment('预支付ID 没有预支付的为空');
            $table->string('transaction_id', 64)->comment('支付平台 如微信返回的单号');
            $table->tinyInteger('status', false, true)
                ->comment('状态 1=支付中 10=支付成功 20=支付失败 30=支付已关闭');
            $table->timestamps();

            $table->unique('out_trade_no');
            $table->index('order_no');
            $table->index('transaction_id');
            $table->index('prepay_id');
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
        Schema::dropIfExists('pay_details');
    }
}
