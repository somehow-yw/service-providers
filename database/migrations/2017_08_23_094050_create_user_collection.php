<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCollection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_collection', function (Blueprint $table) {
            $table->increments('id')->comment('收藏ID');
            $table->unsignedInteger('uid')->comment('用户ID ( 对应 users )');
            $table->unsignedInteger('gid')
                  ->comment('商品ID ( 对应 dp_goods_info )');
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
        Schema::dropIfExists('user_collection');
    }
}
