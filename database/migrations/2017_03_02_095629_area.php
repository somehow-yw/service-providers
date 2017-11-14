<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Area extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('area', function (Blueprint $table) {
            $table->integer('id')->unsigned()->primary();
            $table->integer('pid')->unsigned()->nullable()->index()->comment('父级ID 顶级为空');
            $table->string('node', 64)->nullable()->comment('所有父节点 顶级节点为空');
            $table->string('name', 32)->index()->comment('地区名称');
            $table->tinyInteger('level')->index()->comment('区域等级 值在model中配置');
            $table->tinyInteger('status')->default(0)->index()->comment('可配送状态 值在model中配置');
            $table->float('lat')->comment('伟度');
            $table->float('lng')->comment('经度');
            $table->timestamps();

            $table->index(['lat', 'lng']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('area');
    }
}
