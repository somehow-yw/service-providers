<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterWechatAccountsAddMerchantIdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('wechat_accounts', function (Blueprint $table) {
            $table->string('merchant_id', 64)->default('')->comment('支付商户号');
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
            $table->dropColumn('merchant_id');
        });
    }
}
