<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNodesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nodes', function (Blueprint $table) {
            $table->id();
            $table->ipAddress('ip');
            $table->string('username');
            $table->string('title');
            $table->string('country', 3)->nullable();
            $table->string('password')->nullable();
            $table->string('login_type');//密钥登录还是密码登录
            $table->string('port');
            $table->integer('status')->default(1);
            $table->integer('limit');
            $table->boolean('public')->default(true);
            $table->json('config')->nullable();
            $table->timestamps();

            //2023-1-3 Remove
//            $table->string('bgp')->nullable()->default('frr');//使用的BGP组件
//            $table->integer('asn')->nullable();
            $table->ipAddress('ip6')->nullable();//V6接口Ip
            $table->ipAddress('public_ip')->nullable();//公开显示的V4
            $table->ipAddress('public_ip6')->nullable();//公开显示的V6
        });

        //已经分配的IP
        Schema::create('ip_allocation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tunnel_id')->nullable();
            $table->foreignId('ip_pool_id');
            $table->foreignId('node_id');
            $table->ipAddress('ip');
            $table->integer('cidr');//这个网段的子网網掩
            $table->string('type');//v4 v6
            $table->boolean('intranet')->default(false);//是否是内网地址
            $table->timestamps();
        });

        //IP池设计来自MercyCloud自动化服务 简化而来
        Schema::create('ip_pool', function (Blueprint $table) {
            $table->id();
            $table->foreignId('node_id');
            $table->ipAddress('ip')->unique();
            $table->integer('cidr');//这个网段的子网網掩
            $table->integer('allocation_size')->nullable();//分配的子网
            $table->string('ip_type')->default('ipv6');//ip類型 ipv4 or ipv6
            $table->boolean('intranet')->default(false);//是否是内网地址
            $table->boolean('generated')->default(true);//自动生成分配
            $table->string('type');
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
        Schema::dropIfExists('nodes');
        Schema::dropIfExists('ip_allocation');
        Schema::dropIfExists('ip_pool');
    }
}
