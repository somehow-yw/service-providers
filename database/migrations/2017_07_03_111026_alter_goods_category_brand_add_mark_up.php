<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterGoodsCategoryBrandAddMarkUp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goods_category_brand', function (Blueprint $table) {
            $table->decimal('increase', 6, 3)
                  ->comment('加价数额');
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
            $table->dropColumn('increase');
        });
    }
}
