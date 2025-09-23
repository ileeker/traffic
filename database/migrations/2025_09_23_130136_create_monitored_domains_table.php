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
        Schema::create('monitored_domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_visible')->nullable()->default(null);
            $table->timestamps();
            
            // 索引
            $table->index('domain');
            $table->index('is_visible');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('monitored_domains');
    }
};