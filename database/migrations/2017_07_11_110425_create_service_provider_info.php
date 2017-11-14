<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiceProviderInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_provider_info', function (Blueprint $table) {
            $table->unsignedInteger('sp_id')
                  ->primary()
                  ->comment('service_provider.zdp_user_id');
            $table->string('avatar')
                  ->comment('服务商招牌照片')
                  ->nullable();
            $table->string('delivery_remark')
                  ->comment('配送说明')
                  ->nullable();
            $table->string('introduction')
                  ->comment('服务商店铺介绍')
                  ->nullable();
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
        Schema::dropIfExists('service_provider_info');
    }
}
