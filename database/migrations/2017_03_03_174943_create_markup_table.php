<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarkupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('markup', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('sp_id')->comment('商户id');
            $table->integer('sort_id')->comment('加价的商品分类id');
            $table->decimal('increase', 5, 2)->default(0)->comment('加价金额');
            $table->timestamp('updated_at')
                ->default(\DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))
                ->comment('修改价格时间');

            $table->index(['sp_id','sort_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('markup');
    }
}
