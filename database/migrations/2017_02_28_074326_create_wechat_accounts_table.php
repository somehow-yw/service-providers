<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateWechatAccountsTable.
 * 服务商微信信息表
 */
class CreateWechatAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('wechat_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('sp_id')->unsigned()->comment('服务商ID');
            $table->string('source', 50)->default('')->comment('来源站，为服务商指定的特定域名或其它可验证信息');
            $table->string('wechat_name', 50)->default('')->comment('公众号名');
            $table->string('appid', 32)->default('')->comment('服务商公众号应用ID AppID');
            $table->string('secret', 64)->default('')->comment('服务商公众号应用密钥 AppSecret');
            $table->string('token', 32)->default('')->comment('服务商公众号令牌 Token');
            $table->string('aes_key', 64)->default('')->comment('服务商公众号消息加解密密钥 EncodingAESKey');
            $table->timestamps();
            $table->softDeletes();

            $table->unique('source');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('wechat_accounts');
    }
}
