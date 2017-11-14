<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Zdp\ServiceProvider\Data\Models\ServiceProvider as SP;
use Zdp\ServiceProvider\Data\Models\SpArea;

class CreateSpAreas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('sp_areas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sp_id')->index();
            $table->unsignedInteger('province_id')->index();
            $table->unsignedInteger('city_id')->index()->nullable();
            $table->unsignedInteger('county_id')->index()->nullable();

            $table->unique(['province_id', 'city_id', 'county_id']);
        });

        $sps = Sp::query()
                 ->where('status', Sp::PASS)
                 ->get();

        \DB::transaction(function () use ($sps) {
            foreach ($sps as $sp) {
                SpArea::create([
                    'sp_id'       => $sp->zdp_user_id,
                    'province_id' => $sp->province_id,
                    'city_id'     => $sp->city_id,
                    'county_id'   => $sp->county_id,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sp_areas');
    }
}
