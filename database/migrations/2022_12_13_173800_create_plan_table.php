<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->json('data')->nullable();
            $table->string('description')->nullable();
            $table->bigInteger('limit');
            $table->integer('ipv6_num');
            $table->integer('ipv4_num');
            $table->bigInteger('bandwidth')->nullable();
            $table->bigInteger('traffic');
            $table->timestamps();
        });

        Schema::create('user_plan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id');
            $table->foreignId('user_id');
            $table->dateTime('expire_at');
            $table->dateTime('reset_time'); // Reset Bandwidth Calendar Day
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
        Schema::dropIfExists('plans');
        Schema::dropIfExists('user_plan');
    }
};
