<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoodsBlacklist extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_blacklist', function (Blueprint $table) {
            $table->increments('id')->comment('商品屏蔽ID');
            $table->unsignedInteger('sp_id')->comment('服务商ID')->index();
            $table->unsignedInteger('sort_id')->comment('商品分类ID')->index();
            $table->unsignedInteger('brand_id')->comment('品牌ID')->index();

            $table->unique(['sp_id', 'sort_id', 'brand_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods_blacklist');
    }
}
