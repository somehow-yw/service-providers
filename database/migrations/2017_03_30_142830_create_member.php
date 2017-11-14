<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMember extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sp_member', function (Blueprint $table) {
            $table->string('wechat_openid', 32)->primary()->comment('微信openid');
            $table->string('wechat_name', 32)->comment('微信用户名');
            $table->integer('sp_id')->index()->comment('所属店铺ID');

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
        Schema::dropIfExists('sp_member');
    }
}
