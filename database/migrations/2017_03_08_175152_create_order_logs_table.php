<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrderLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->unsigned()->comment('订单ID');
            $table->integer('operation')->unsigned()->comment('操作，对应order表的status定义');
            $table->tinyInteger('source')->unsigned()->comment('操作来源 表示是谁做的操作 1=买家 2=服务商 3=管理者 ...');
            $table->integer('user_id')->unsigned()->comment('操作者ID 如：服务商ID，会员ID，管理者ID等');
            $table->timestamps();

            $table->index('order_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_logs');
    }
}
