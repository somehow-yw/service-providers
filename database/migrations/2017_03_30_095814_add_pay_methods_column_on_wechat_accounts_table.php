<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPayMethodsColumnOnWechatAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wechat_accounts', function (Blueprint $table) {
            $table->string('pay_methods', 255)->default('1')->comment('支付方式id 半角逗号分隔');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('wechat_accounts', function (Blueprint $table) {
            $table->dropColumn('pay_methods');
        });
    }
}
