<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateShopTypeTable.
 * 店铺类型表
 */
class CreateShopTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shop_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type_name', 20)->comment('类型名称');
            $table->tinyInteger('sort_value')->unsigned()->default(1)->comment('排序');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shop_type');
    }
}
