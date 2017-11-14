<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableHomeItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('home_item', function (Blueprint $table) {
            $table->increments('id')
                  ->comment('服务商首页项ID');
            $table->unsignedInteger('sp_id')
                  ->comment('服务商ID');
            $table->tinyInteger('type')
                  ->index()
                  ->comment('服务商首页项类型, 1:热销分类 2:热门品牌 3:推荐单品');
            $table->unsignedInteger('ref_id')
                  ->comment('服务商首页项关联ID, 根据类型关联分类, 品牌, 商品');
            $table->unsignedSmallInteger('sort')
                  ->comment('排序');
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
        Schema::dropIfExists('home_item');
    }
}
