<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tunnel_traffic', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tunnel_id')->nullable();
            $table->foreignId('user_id');
            $table->bigInteger('in')->default(0);//入网流量
            $table->bigInteger('out')->default(0);//出网流量
            //deadline
            $table->dateTime('deadline')->nullable();
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
        Schema::dropIfExists('tunnel_traffic');
    }
};
