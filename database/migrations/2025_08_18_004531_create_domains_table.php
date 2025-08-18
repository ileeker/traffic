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
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 255)->unique();
            $table->integer('current_ranking')->nullable();
            $table->json('ranking_data')->nullable();
            $table->timestamp('last_updated')->nullable();
            $table->timestamps();
            
            // 索引
            $table->index('domain');
            $table->index('last_updated');
            $table->index('current_ranking');
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
    }
};