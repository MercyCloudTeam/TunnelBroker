<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateNodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->ipAddress('ip6')->nullable();//V6接口Ip
            $table->ipAddress('public_ip')->nullable();//公开显示的V4
            $table->ipAddress('public_ip6')->nullable();//公开显示的V6
        });

        Schema::create('analysis',function (Blueprint $table){
            $table->id();
            $table->string('type',16);//不同类型的报告
            $table->json('data');
            $table->integer('status')->default(1);
            $table->bigInteger('type_id');//关联不同类型的ID
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
        Schema::dropIfExists('analysis');
    }
}
