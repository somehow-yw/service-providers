<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sp_messages', function (Blueprint $table) {
            $table->increments('id')->comment('自增长ID');
            $table->integer('shid')->comment('用户ID');
            $table->string('message', 500)->comment('用户反馈的内容');
            $table->dateTime('mesgtime')->comment('用户反馈的时间');
            $table->string('formip', 64)->comment('用户IP');
            $table->tinyInteger('msgact')->default(0)->comment('处理状态');
            $table->smallInteger('toid')->nullable()->comment('处理者ID');
            $table->string('yijian', 500)->nullable()->comment('处理者的回复内容');
            $table->dateTime('cltime')->nullable()->comment('处理时间');
            
            $table->index('shid');
            $table->index('toid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sp_messages');
    }
}
