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
        Schema::create('new_domain_rankings', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 255)->unique()->comment('域名');
            $table->bigInteger('current_ranking')->unsigned()->nullable()->comment('当前排名');
            
            // 日变化相关字段
            $table->bigInteger('daily_change')->nullable()->comment('日变化数值');
            $table->enum('daily_trend', ['up', 'down', 'stable'])->nullable()->comment('日变化趋势');
            
            // 周变化相关字段
            $table->bigInteger('week_change')->nullable()->comment('周变化数值');
            $table->enum('week_trend', ['up', 'down', 'stable'])->nullable()->comment('周变化趋势');
            
            // 两周变化相关字段
            $table->bigInteger('biweek_change')->nullable()->comment('两周变化数值');
            $table->enum('biweek_trend', ['up', 'down', 'stable'])->nullable()->comment('两周变化趋势');
            
            // 三周变化相关字段
            $table->bigInteger('triweek_change')->nullable()->comment('三周变化数值');
            $table->enum('triweek_trend', ['up', 'down', 'stable'])->nullable()->comment('三周变化趋势');
            
            $table->datetime('registered_at')->nullable()->comment('域名注册日期');
            $table->boolean('is_visible')->nullable()->comment('是否显示');
            $table->json('metadata')->nullable()->comment('元数据(包含类别、语言、介绍等)');
            
            $table->timestamps();
            
            // 添加索引
            $table->index('current_ranking');
            $table->index('is_visible');
            $table->index('registered_at');
            $table->index(['is_visible', 'current_ranking']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('new_domain_rankings');
    }
};