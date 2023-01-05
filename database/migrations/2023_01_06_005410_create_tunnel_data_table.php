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
        Schema::create('tunnel_bandwidth', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tunnel_id');
            $table->bigInteger('in')->default(0);//入网流量
            $table->bigInteger('out')->default(0);//出网流量
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
        Schema::dropIfExists('tunnel_bandwidth');
    }
};
