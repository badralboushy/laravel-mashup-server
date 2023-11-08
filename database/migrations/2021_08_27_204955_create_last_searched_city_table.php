<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLastSearchedCityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('last_searched_city', function (Blueprint $table) {
            $table->id();
            $table->String('user_id');
            $table->json('restaurants')->nullable();
            $table->json('hotels')->nullable();
            $table->json('location_restaurants')->nullable();
            $table->json('location_hotels')->nullable();
            $table->timestamps();
        });
    }// location_restaurants

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('last_searched_city');
    }
}
