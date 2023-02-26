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
        Schema::create('bgp_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tunnel_id');
            $table->foreignId('asn_id');
            $table->foreignId('user_id');
            $table->integer('status')->nullable();
            $table->integer('limit')->nullable(); //默认按照ASN表的写一次，但可以意外修改
            $table->json('session')->nullable();
            $table->json('routes')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        Schema::create('links',function (Blueprint $table){
            $table->id();
            $table->string('category')->nullable();
            $table->string('title');
            $table->string('url');
            $table->longText('description')->nullable();
            $table->string('logo')->nullable();
            $table->longText('display')->nullable();
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
        Schema::dropIfExists('bgp_sessions');
        Schema::dropIfExists('links');
    }
};
