<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameGoodsBlacklist extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goods_blacklist', function (Blueprint $table) {
            $table->unsignedTinyInteger('display')
                  ->comment('显示类型')
                  ->index()
                  ->after('brand_id');
            $table->unsignedInteger('serial_no')
                  ->comment('排序序号(第几位)')
                  ->after('display');
        });

        Schema::table('goods_blacklist', function (Blueprint $table) {
            $table->rename('goods_category_brand');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('goods_category_brand', function (Blueprint $table) {
            $table->dropColumn('serial_no');
            $table->dropColumn('display');

            $table->rename('goods_blacklist');
        });
    }
}
