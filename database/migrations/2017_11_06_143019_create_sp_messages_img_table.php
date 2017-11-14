<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSpMessagesImgTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sp_messages_img', function ($table) {
            $table->increments('id');
            $table->integer('message_id')->comment('反馈id');
            $table->string('img_url')->comment('反馈的图片地址');
            $table->tinyInteger('type')->default(0)->comment('类型：默认0');
            $table->timestamps();

            $table->index('id');
            $table->index('message_id');
            $table->index('img_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sp_messages_img');
    }
}
