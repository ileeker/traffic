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
        Schema::create('ranking_changes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('domain_id');
            $table->date('record_date');
            $table->integer('current_ranking');
            
            // 周变化
            $table->integer('week_change')->nullable();
            $table->enum('week_trend', ['up', 'down', 'stable'])->default('stable');
            
            // 月变化
            $table->integer('month_change')->nullable();
            $table->enum('month_trend', ['up', 'down', 'stable'])->default('stable');
            
            // 季度变化
            $table->integer('quarter_change')->nullable();
            $table->enum('quarter_trend', ['up', 'down', 'stable'])->default('stable');
            
            // 年变化
            $table->integer('year_change')->nullable();
            $table->enum('year_trend', ['up', 'down', 'stable'])->default('stable');
            
            $table->timestamps();
            
            // 外键约束
            $table->foreign('domain_id')->references('id')->on('domains')->onDelete('cascade');
            
            // 索引
            $table->index(['domain_id', 'record_date']);
            $table->index('record_date');
            $table->unique(['domain_id', 'record_date']); // 确保同一域名同一天只有一条记录
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ranking_changes');
    }
};