<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableWebAdminLoginToken extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_admin_login_token', function (Blueprint $table) {
            $table->string('token', 8)->primary()->comment('登陆凭证');
            $table->string('open_id', 32)->comment('关联的 OPEN ID')->unique();
            $table->timestamp('expired_at')->index()->comment('过期时间');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('web_admin_login_token');
    }
}
