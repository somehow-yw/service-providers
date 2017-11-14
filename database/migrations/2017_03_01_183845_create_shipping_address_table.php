<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateShippingAddressTable.
 * 会员收货信息表
 */
class CreateShippingAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shipping_address', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->comment('用户id');
            $table->integer('province_id')->unsigned()->comment('所在省对应ID');
            $table->integer('city_id')->unsigned()->comment('所在市对应ID');
            $table->integer('county_id')->unsigned()->nullable()->comment('所在县对应ID');
            $table->string('address', 255)->comment('收货地址');
            $table->string('receiver', 64)->comment('收货人');
            $table->string('mobile', 15)->comment('收货人电话');
            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('shipping_address');
    }
}
