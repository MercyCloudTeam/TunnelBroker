<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTunnelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tunnels', function (Blueprint $table) {
            $table->id();
            $table->string('mode',20)->default('sit');//gre sit ipip
            $table->ipAddress('local')->nullable();//若无特殊指定则按Node的
            $table->ipAddress('remote');
            $table->ipAddress('ip4')->nullable();
            $table->integer('ip4_cidr')->default(30);//这个网段的子网網掩
            $table->string('ip4_rdns')->nullable();
            $table->ipAddress('ip6')->nullable();
            $table->integer('ip6_cidr')->default(64);
            $table->string('ip6_rdns')->nullable();
            $table->string('key',256)->nullable();
            $table->integer('ttl')->nullable();
            $table->integer('vlan')->nullable();
            $table->integer('status')->default(1);
            $table->macAddress('mac')->nullable();
            $table->string('interface')->nullable();
            $table->integer('srcport')->nullable();
            $table->integer('dstport')->nullable();//预留给VXLAN等其他端口协议使用
            $table->string('in')->default("0");//入网流量
            $table->string('out')->default("0");//出网流量
            $table->json('config')->nullable();
            $table->bigInteger('asn_id')->nullable();
            $table->bigInteger('user_id');
            $table->bigInteger('node_id');
            $table->softDeletes();//软删除
            $table->timestamps();
        });

        //问题 通过AS-Set包含过滤 若用户的AS-set包含了 Tier1 造成“路由倒灌”
        //在自动生成过滤器的基础上先限制用户发送的路由条数

        Schema::create('bgp_filter',function (Blueprint $table){
           $table->id();
           $table->bigInteger('user_id');//归属用户
           $table->string('type');//ASN还是AS-SET还是IP段
           $table->string('resources');
           $table->timestamps();
        });//BGP过滤器


        Schema::create('asn',function (Blueprint $table){
            $table->id();
            $table->bigInteger('user_id');//归属用户 相当于Owner
            $table->bigInteger('limit')->default(20);//路由条目限制
            $table->boolean('validate')->default(0);//验证状态
            $table->bigInteger('asn')->unique();//唯一 不允许多个用户验证同一个ASN
            $table->string('loa')->nullable();//LOA文件验证
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();//邮件验证时间
            $table->timestamps();
        });
        //TODO 将ASN授权给团队
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tunnels');
        Schema::dropIfExists('bgp_filter');
        Schema::dropIfExists('asn');
    }
}
