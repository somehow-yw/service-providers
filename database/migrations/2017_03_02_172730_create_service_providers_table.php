<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_providers', function (Blueprint $table) {
            $table->integer('zdp_user_id')->primary()->comment('找冻品用户id');
            $table->string('shop_name', 20)->comment('店铺名');
            $table->string('user_name', 20)->comment('联系人');
            $table->string('mobile', 15)->comment('手机号');
            $table->tinyInteger('status')->comment('状态');
            $table->integer('province_id')->unsigned()->default(0)->comment('所在省对应ID');
            $table->integer('city_id')->unsigned()->default(0)->comment('所在市对应ID');
            $table->integer('county_id')->unsigned()->default(0)->comment('所在县对应ID');
            $table->string('address', 255)->comment('卖家地址');
            $table->string('market_ids', 255)->default('')->comment('服务市ID串 半角逗号分隔');
            $table->string('wechat_openid', 32)->nullable()->comment('微信open_id');

            $table->timestamps();
            $table->softDeletes();

            $table->index('wechat_openid');
            $table->index(['mobile', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('service_providers');
    }
}
