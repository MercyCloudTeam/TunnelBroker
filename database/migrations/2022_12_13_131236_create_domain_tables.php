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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('domain');
            $table->string('status')->default('active');
            $table->string('description')->nullable();
            $table->foreignId('user_id')->nullable();
            $table->timestamps();
        });

        Schema::create('domain_resource_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id');
            $table->string('name');
            $table->string('type');
            $table->string('content');
            $table->integer('ttl')->default(3600);
            $table->integer('priority')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('nodes_components', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('node_id');
            $table->string('component');
            $table->json('data');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('nodes_components_data', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('node_id');
            $table->string('component');
            $table->string('key');
            $table->string('value');
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
        Schema::dropIfExists('domains');
        Schema::dropIfExists('domain_resource_records');
        Schema::dropIfExists('nodes_components');
        Schema::dropIfExists('nodes_components_data');
    }
};
