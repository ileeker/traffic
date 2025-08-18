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
            $table->bigIncrements('id');
            $table->string('domain', 255)->unique('domain_unique');
            $table->date('record_date');
            $table->integer('current_ranking');
            
            // 周变化
            $table->integer('week_change')->nullable();
            $table->enum('week_trend', ['up', 'down', 'stable'])->nullable();
            
            // 月变化
            $table->integer('month_change')->nullable();
            $table->enum('month_trend', ['up', 'down', 'stable'])->nullable();
            
            // 季度变化
            $table->integer('quarter_change')->nullable();
            $table->enum('quarter_trend', ['up', 'down', 'stable'])->nullable();
            
            // 年变化
            $table->integer('year_change')->nullable();
            $table->enum('year_trend', ['up', 'down', 'stable'])->nullable();
            
            $table->timestamps();

            // 主要索引优化
            // 1. 域名索引（已通过unique创建）
            
            // 2. 今日记录查询的复合索引（最常用）
            $table->index('record_date', 'idx_record_date');
            
            // 3. 今日+周变化查询的复合索引
            $table->index(['record_date', 'week_change'], 'idx_date_week_change');
            $table->index(['record_date', 'week_trend'], 'idx_date_week_trend');
            
            // 4. 今日+月变化查询的复合索引
            $table->index(['record_date', 'month_change'], 'idx_date_month_change');
            $table->index(['record_date', 'month_trend'], 'idx_date_month_trend');
            
            // 5. 今日+季度变化查询的复合索引
            $table->index(['record_date', 'quarter_change'], 'idx_date_quarter_change');
            $table->index(['record_date', 'quarter_trend'], 'idx_date_quarter_trend');
            
            // 6. 今日+年变化查询的复合索引
            $table->index(['record_date', 'year_change'], 'idx_date_year_change');
            $table->index(['record_date', 'year_trend'], 'idx_date_year_trend');
            
            // 7. 单独的变化值索引（用于排序和范围查询）
            $table->index('week_change', 'idx_week_change');
            $table->index('month_change', 'idx_month_change');
            $table->index('quarter_change', 'idx_quarter_change');
            $table->index('year_change', 'idx_year_change');
            
            // 8. 外键约束（可选）
            // $table->foreign('domain')->references('domain')->on('domains')->onUpdate('cascade')->onDelete('cascade');
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