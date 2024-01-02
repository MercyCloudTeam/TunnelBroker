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
        Schema::create('vrf',function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->unsignedBigInteger('node_id');
            //RT RD
            $table->string('rt')->nullable();
            $table->string('rd')->nullable();

            $table->timestamps();
        });

        Schema::create('isis',function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('node_id');
            $table->string('area');
            $table->string('level');

            $table->timestamps();
        });

        //节点互联
        Schema::create('node_connect',function (Blueprint $table){
            $table->id();
            //关联Tunnel ID
            $table->unsignedBigInteger('tunnel_id');
            //关联的两个节点ID
            $table->unsignedBigInteger('right_node_id');
            $table->unsignedBigInteger('left_node_id');
            //关联的两个节点的端口

            $table->integer('status')->default(1);

            //选路影响开销 （MED/Cost）
            $table->integer('cost')->nullable();

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
        Schema::dropIfExists('isis');
        Schema::dropIfExists('vrf');
        Schema::dropIfExists('node_connect');
    }
};
