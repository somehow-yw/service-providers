<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateUsersTable.
 * 会员信息表
 */
class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sp_id')->unsigned()->comment('服务商ID');
            $table->string('wechat_openid', 32)->comment('会员关注当前公众号的微信OPENID');
            $table->string('wechat_nickname', 50)->default('')->comment('微信昵称');
            $table->string('wechat_avatar', 255)->default('')->comment('微信头像');
            $table->string('mobile_phone', 11)->comment('注册手机号');
            $table->string('user_name', 10)->default('')->comment('会员真实姓名');
            $table->string('shop_name', 50)->default('')->comment('店铺名称');
            $table->integer('shop_type_id')->unsigned()->default(0)->comment('店铺类型对应ID');
            $table->integer('province_id')->unsigned()->default(0)->comment('所在省对应ID');
            $table->integer('city_id')->unsigned()->default(0)->comment('所在市对应ID');
            $table->integer('county_id')->unsigned()->default(0)->comment('所在县对应ID');
            $table->string('address', 255)->default(0)->comment('地址');
            $table->integer('status')->unsigned()->default(0)->comment('会员状态 对应着MODEL配置');
            $table->integer('shipping_address_id')->unsigned()->default(0)->comment('会员默认收货地址ID');
            $table->timestamps();
            $table->softDeletes();

            $table->index('wechat_openid');
            $table->index('mobile_phone');
            $table->index('shop_name');
            $table->index(['sp_id', 'shop_type_id']);
            $table->index(['shop_type_id', 'shop_name']);
            $table->index(['province_id', 'shop_type_id']);
            $table->index(['province_id', 'city_id', 'shop_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
