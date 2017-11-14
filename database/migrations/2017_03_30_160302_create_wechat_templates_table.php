<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * 微信消息模板
 * Class CreateWechatTemplatesTable
 */
class CreateWechatTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->string('source', 32)->comment('服务商标识 二级域名');
            $table->string('short_id', 32)->comment('微信模板编号 微信的模板短ID');
            $table->string('template_id', 64)->comment('已添加模板的模板ID 应用模板ID');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wechat_templates');
    }
}
