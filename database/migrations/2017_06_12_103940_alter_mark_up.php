<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterMarkUp extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('markup', function (Blueprint $table) {
            // 添加加价类型 0 固定增加 1 百分比增加
            $table->decimal('increase', 6, 3)
                  ->comment('加价数额')
                  ->change();
            $table->unsignedTinyInteger('type')
                  ->after('increase')
                  ->default(0)
                  ->comment('加价类型');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('markup', function (Blueprint $table) {
            $table->decimal('increase', 5, 2)
                  ->comment('加价金额')
                  ->change();
            $table->dropColumn('type');
        });
    }
}
