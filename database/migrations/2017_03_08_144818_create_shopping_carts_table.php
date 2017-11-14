<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * 会员购物车表
 * Class CreateShoppingCartsTable
 */
class CreateShoppingCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shopping_carts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->comment('会员ID');
            $table->integer('goods_id')->unsigned()->comment('商品ID');
            $table->integer('buy_num')->unsigned()->comment('购买数量');
            $table->timestamps();

            $table->unique(['user_id', 'goods_id']);
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
        Schema::dropIfExists('shopping_carts');
    }
}
